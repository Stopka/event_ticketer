<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;


use App\Model\ApplicationPdfManager;
use App\Model\Exception\NotReadyException;
use App\Model\Persistence\Entity\CartEntity;
use Kdyby\Events\Subscriber;
use Nette\Mail\SendException;
use Nette\SmartObject;

class CartCreatedNotifier implements Subscriber {
    use SmartObject, TEmailService;

    /** @var  ApplicationPdfManager */
    private $applicationPdfManager;

    /**
     * CartCreatedNotifier constructor.
     * @param EmailService $emailService
     * @param ApplicationPdfManager $applicationPdfManager
     */
    public function __construct(EmailService $emailService, ApplicationPdfManager $applicationPdfManager) {
        $this->injectEmailService($emailService);
        $this->applicationPdfManager = $applicationPdfManager;
    }

    /**
     * Event callback
     * @param CartEntity $cartEntity
     */
    public function onCartCreated(CartEntity $cartEntity){
        $this->sendNotification($cartEntity);
    }

    /**
     * @param CartEntity $cartEntity
     * @throws NotReadyException
     * @throws SendException
     */
    public function sendNotification(CartEntity $cartEntity): void {
        if (!$cartEntity->getEmail()) {
            throw new NotReadyException("Cart has no email address");
        }
        $emailService =$this->getEmailService();
        $link = $emailService->generateLink('Front:Cart:', ['id' => $cartEntity->getHashId()]);
        $message = $emailService->createMessage();
        $message->addTo($cartEntity->getEmail(), $cartEntity->getFullName());
        $message->setSubject('Přihláška na ' . $cartEntity->getEvent()->getName());
        $message->setHtmlBody("<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na <strong>" . $cartEntity->getEvent()->getName() . "</strong>. V příloze zasíláme přihlášku, bezinfekčnost a lékařské potvrzení. Bezinfekčnost, lékařské potvrzení a list s informacemi můžete v případě ztráty získat na našich stránkách.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé rezervované místo, dovyplnit, odeslat a ke každé přihlášce zaplatit rezervační poplatek. Další informace jsou uvedeny přímo v přihlášce.</p>
<p>Aktuální stav Vašich přihlášek můžete průběžně sledovat na následující adrese: <br />
 <a href='$link'>$link</a></p>
<p>V případě dotazu pište na ldtmpp@email.cz.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa.</em></p>");
        foreach ($cartEntity->getApplications() as $application) {
            $file_path = $this->applicationPdfManager->getPdfPath($application);
            $message->addAttachment('přihláška_' . $application->getId() . '.pdf', @file_get_contents($file_path));
        }
        foreach ($this->applicationPdfManager->getFilePaths($cartEntity->getEvent()) as $file) {
            $message->addAttachment($file);
        }
        $emailService->sendMessage($message);
    }

    public function getSubscribedEvents() {
        return ['CartManager::onCartCreated'];
    }
}