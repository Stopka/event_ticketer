<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;

use App\Model\Exception\NotReadyException;
use App\Model\Persistence\Entity\ReservationEntity;
use App\Model\Persistence\Manager\ReservationManager;
use Kdyby\Events\Subscriber;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\SendException;
use Nette\SmartObject;

class ReservationDelegatedNotifier implements Subscriber {
    use SmartObject, TEmailService;

    /**
     * CartCreatedNotifier constructor.
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService) {
        $this->injectEmailService($emailService);
    }

    /**
     * Event callback
     * @param ReservationEntity $reservationEntity
     * @throws InvalidLinkException
     */
    public function onReservationDelegated(ReservationEntity $reservationEntity){
        $this->sendNotification($reservationEntity);
    }

    /**
     * @param ReservationEntity $reservationEntity
     * @throws NotReadyException
     * @throws SendException
     * @throws InvalidLinkException
     */
    public function sendNotification(ReservationEntity $reservationEntity): void {
        $emailService =$this->getEmailService();
        $link = $emailService->generateLink('Front:Reservation:', ['id' => $reservationEntity->getUid()]);
        $message = $emailService->createMessage();
        $message->addTo($reservationEntity->getEmail(), $reservationEntity->getFullName());
        $message->setSubject('Rezervace místa na ' . $reservationEntity->getEvent()->getName());
        $message->setHtmlBody("<p>Dobrý den,</p>
<p>Rádi bychom Vám oznámili, že Vám bylo rezervováno místo na <strong>" . $reservationEntity->getEvent()->getName() . "</strong>. Přihlášku získáte po registraci na následující adrese: <br />
<a href='$link'>$link</a></p>
<p>Vyplňte prosím registraci co nejdříve.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa.</em></p>");
        $emailService->sendMessage($message);
    }

    public function getSubscribedEvents() {
        return [ReservationManager::class . '::onReservationDelegated'];
    }
}