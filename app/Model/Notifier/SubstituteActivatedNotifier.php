<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;


use App\Model\Exception\NotReadyException;
use App\Model\Persistence\Entity\SubstituteEntity;
use Kdyby\Events\Subscriber;
use Nette\Mail\SendException;
use Nette\SmartObject;

class SubstituteActivatedNotifier implements Subscriber {
    use SmartObject, TEmailService;

    /**
     * SubstituteActivatedNotifier constructor.
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService) {
        $this->injectEmailService($emailService);
    }


    /**
     * Event callback
     * @param SubstituteEntity $substituteEntity
     */
    public function onSubstituteActivated(SubstituteEntity $substituteEntity): void {
        try {
            $this->sendNotification($substituteEntity);
        } catch (NotReadyException $exception){

        }
    }

    /**
     * @param SubstituteEntity $substitute
     * @throws NotReadyException
     * @throws SendException
     */
    public function sendNotification(SubstituteEntity $substitute): void {
        if (!$substitute->getEmail()) {
            throw new NotReadyException("Substitute has no email address");
        }
        if (!$substitute->isActive()) {
            throw new NotReadyException('Náhradník není aktivní.');
        }
        $emailService = $this->getEmailService();
        $link = $emailService->generateLink('Front:Substitute:', ['id' => $substitute->getId()]);
        $message = $emailService->createMessage();
        $message->addTo($substitute->getEmail(), $substitute->getFullName());
        $message->setSubject('Uvolněné místo na ' . $substitute->getEvent()->getName());

        $message_body = "<p>Dobrý den,</p>
<p>S potěšením oznamujeme, že se pro Vás uvolnilo místo na <strong>" . $substitute->getEvent()->getName() . "</strong>. Přihlášku získáte po registraci na následující adrese: <br />
<a href='$link'>$link</a></p>";
        $endDate = $substitute->getEndDate() ? $substitute->getEndDate()->format('d.m.Y H:i:s') : null;
        $message_endDate = ($endDate ? "<p>Místo pro vás držíme do $endDate, poté dáme šanci dalšímu náhradníkovi v pořadí.</p>" : "");
        $message_foot = "<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa náhradníka.</em></p>";

        $message->setHtmlBody($message_body . $message_endDate . $message_foot);
        $emailService->sendMessage($message);
    }

    public function getSubscribedEvents() {
        return ['SubstituteManager::onSubstituteActivated'];
    }
}