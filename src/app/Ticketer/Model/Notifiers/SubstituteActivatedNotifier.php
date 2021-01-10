<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Database\Managers\Events\SubstituteActivatedEvent;
use Ticketer\Model\Exceptions\NotReadyException;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\Managers\SubstituteManager;
use Nette\Mail\SendException;
use Nette\SmartObject;

class SubstituteActivatedNotifier implements EventSubscriberInterface
{
    use SmartObject;
    use TEmailService;

    /**
     * SubstituteActivatedNotifier constructor.
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService)
    {
        $this->injectEmailService($emailService);
    }


    /**
     * Event callback
     * @param SubstituteActivatedEvent $event
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function onSubstituteActivated(SubstituteActivatedEvent $event): void
    {
        try {
            $this->sendNotification($event->getSubstitute());
        } catch (NotReadyException $exception) {
        }
    }

    /**
     * @param SubstituteEntity $substitute
     * @throws NotReadyException
     * @throws SendException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function sendNotification(SubstituteEntity $substitute): void
    {
        if (null === $substitute->getEmail()) {
            throw new NotReadyException("Substitute has no email address");
        }
        if (!$substitute->isActive()) {
            throw new NotReadyException('Náhradník není aktivní.');
        }
        $emailService = $this->getEmailService();
        $link = $emailService->generateLink('Front:Substitute:', ['id' => $substitute->getId()->toString()]);
        $message = $emailService->createMessage();
        $message->addTo($substitute->getEmail(), $substitute->getFullName());
        $event = $substitute->getEvent();
        if (null === $event) {
            throw new NotReadyException('Missing event');
        }
        $message->setSubject('Uvolněné místo na ' . $event->getName());

        $message_body = "<p>Dobrý den,</p>
<p>S potěšením oznamujeme, že se pro Vás uvolnilo místo na
<strong>" . $event->getName() . "</strong>.
Přihlášku získáte po registraci na následující adrese: <br />
<a href='$link'>$link</a></p>";
        $endDate = null !== $substitute->getEndDate() ? $substitute->getEndDate()->format('d.m.Y H:i:s') : null;
        $message_endDate = (null !== $endDate ? "
<p>Místo pro vás držíme do $endDate, poté dáme šanci dalšímu náhradníkovi v pořadí.</p>" : "");
        $message_foot = "<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz
na základě uvolnění místa pro náhradníka.</em></p>";

        $message->setHtmlBody($message_body . $message_endDate . $message_foot);
        $emailService->sendMessage($message);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [SubstituteActivatedEvent::class => 'onSubstituteActivated'];
    }
}
