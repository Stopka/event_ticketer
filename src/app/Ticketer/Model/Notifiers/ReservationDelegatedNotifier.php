<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Database\Managers\Events\ReservationDelegatedEvent;
use Ticketer\Model\Exceptions\NotReadyException;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Database\Managers\ReservationManager;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\SendException;
use Nette\SmartObject;

class ReservationDelegatedNotifier implements EventSubscriberInterface
{
    use SmartObject;
    use TEmailService;

    /**
     * CartCreatedNotifier constructor.
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService)
    {
        $this->injectEmailService($emailService);
    }

    /**
     * Event callback
     * @param ReservationDelegatedEvent $event
     * @throws InvalidLinkException
     */
    public function onReservationDelegated(ReservationDelegatedEvent $event): void
    {
        $this->sendNotification($event->getReservation());
    }

    /**
     * @param ReservationEntity $reservationEntity
     * @throws NotReadyException
     * @throws SendException
     * @throws InvalidLinkException
     */
    public function sendNotification(ReservationEntity $reservationEntity): void
    {
        $emailService = $this->getEmailService();


        $event = $reservationEntity->getEvent();
        if (null === $event) {
            throw new RuntimeException('Missing event');
        }
        $email = $reservationEntity->getEmail();
        if (null === $email) {
            throw new RuntimeException('Missing email address');
        }

        $link = $emailService->generateLink('Front:Reservation:', ['id' => $reservationEntity->getId()->toString()]);
        $message = $emailService->createMessage();
        $message->addTo($email, $reservationEntity->getFullName());
        $message->setSubject('Rezervace místa na ' . $event->getName());

        $message->setHtmlBody(
            "<p>Dobrý den,</p>
<p>Rádi bychom Vám oznámili, že Vám bylo rezervováno místo na
<strong>" . $event->getName() . "</strong>.
Přihlášku získáte po registraci na následující adrese: <br />
<a href='$link'>$link</a></p>
<p>Vyplňte prosím registraci co nejdříve.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek humo.cz
na základě rezervace místa.</em></p>"
        );
        $emailService->sendMessage($message);
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ReservationDelegatedEvent::class => 'onReservationDelegated'];
    }
}
