<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;


use App\Model\Exception\NotReadyException;
use App\Model\IApplicationPdfManager;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Manager\CartManager;
use Kdyby\Events\Subscriber;
use Nette\Mail\SendException;
use Nette\SmartObject;

class CartCreatedNotifier implements Subscriber {
    use SmartObject, TEmailService, TAtachmentManager;

    /** @var  IApplicationPdfManager */
    private $applicationPdfManager;

    /**
     * CartCreatedNotifier constructor.
     * @param EmailService $emailService
     * @param IApplicationPdfManager $applicationPdfManager
     * @param IEventMessageAtachmentManagerFactory $atachmentManagerFactory
     */
    public function __construct(
        EmailService $emailService,
        IApplicationPdfManager $applicationPdfManager,
        IEventMessageAtachmentManagerFactory $atachmentManagerFactory
    ) {
        $this->injectEmailService($emailService);
        $this->injectAtachmentManagerFactory($atachmentManagerFactory);
        $this->applicationPdfManager = $applicationPdfManager;
    }

    function getAtachmentManagerNamespace(): string {
        return "CartCreated";
    }


    /**
     * Event callback
     * @param CartEntity $cartEntity
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function onCartCreated(CartEntity $cartEntity) {
        $this->sendNotification($cartEntity);
    }

    /**
     * @param CartEntity $cartEntity
     * @throws NotReadyException
     * @throws SendException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function sendNotification(CartEntity $cartEntity): void {
        if (!$cartEntity->getEmail()) {
            throw new NotReadyException("Cart has no email address");
        }
        $emailService = $this->getEmailService();
        $link = $emailService->generateLink('Front:Cart:', ['id' => $cartEntity->getUid()]);
        $message = $emailService->createMessage();
        $message->addTo($cartEntity->getEmail(), $cartEntity->getFullName());
        $message->setSubject('Přihláška na ' . $cartEntity->getEvent()->getName());
        $message->setHtmlBody("<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na <a href='https://ldtpardubice.cz/event/612'>" . $cartEntity->getEvent()->getName() . "</a>. V příloze zasíláme přihlášku, prohlášení o bezinfekčnosti, posudek o zdravotni způsobilosti a list se základními informacemi. Bezinfekčnost, lékařský posudek a list s informacemi můžete v případě ztráty získat na našich <a href='https://ldtpardubice.cz/article/613'>stránkách</a>.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé registrované místo, dovyplnit, odeslat a ke každé přihlášce zaplatit rezervační poplatek. Další informace jsou uvedeny přímo v přiloženém dokumentu popřípadě na našich stránkách.</p>
<p>Aktuální stav Vašich přihlášek můžete průběžně sledovat na následující adrese: <br />
 <a href='$link'>$link</a></p>
<p>V případě dotazu pište na <a href='mailto:ldtmpp@email.cz'>ldtmpp@email.cz</a>.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě registrace přihlášky.</em></p>");
        foreach ($cartEntity->getApplications() as $application) {
            $this->applicationPdfManager->addMessageAttachment($message, $application);
        }
        $atachmentManager = $this->getAtachmentManager($cartEntity->getEvent());
        $atachmentManager->addAttachmentsToMessage($message);
        $emailService->sendMessage($message);
    }

    public function getSubscribedEvents() {
        return [CartManager::class . '::onCartCreated'];
    }
}