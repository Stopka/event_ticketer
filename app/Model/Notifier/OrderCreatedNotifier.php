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
use App\Model\Persistence\Entity\OrderEntity;
use Kdyby\Events\Subscriber;
use Nette\Mail\SendException;
use Nette\Object;

class OrderCreatedNotifier extends Object implements Subscriber {
    use TEmailService;

    /** @var  ApplicationPdfManager */
    private $applicationPdfManager;

    /**
     * OrderCreatedNotifier constructor.
     * @param EmailService $emailService
     * @param ApplicationPdfManager $applicationPdfManager
     */
    public function __construct(EmailService $emailService, ApplicationPdfManager $applicationPdfManager) {
        $this->injectEmailService($emailService);
        $this->applicationPdfManager = $applicationPdfManager;
    }

    /**
     * Event callback
     * @param OrderEntity $orderEntity
     */
    public function onOrderCreated(OrderEntity $orderEntity){
        $this->sendNotification($orderEntity);
    }

    /**
     * @param OrderEntity $orderEntity
     * @throws NotReadyException
     * @throws SendException
     */
    public function sendNotification(OrderEntity $order): void {
        if (!$order->getEmail()) {
            throw new NotReadyException("Order has no email address");
        }
        $emailService =$this->getEmailService();
        $link = $emailService->generateLink('Front:Order:', ['id' => $order->getHashId()]);
        $message = $emailService->createMessage();
        $message->addTo($order->getEmail(), $order->getFullName());
        $message->setSubject('Přihláška na ' . $order->getEvent()->getName());
        $message->setHtmlBody("<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na <strong>" . $order->getEvent()->getName() . "</strong>. V příloze zasíláme přihlášku, bezinfekčnost a lékařské potvrzení. Bezinfekčnost, lékařské potvrzení a list s informacemi můžete v případě ztráty získat na našich stránkách.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé rezervované místo, dovyplnit, odeslat a ke každé přihlášce zaplatit rezervační poplatek. Další informace jsou uvedeny přímo v přihlášce.</p>
<p>Aktuální stav Vašich přihlášek můžete průběžně sledovat na následující adrese: <br />
 <a href='$link'>$link</a></p>
<p>V případě dotazu pište na ldtmpp@email.cz.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa.</em></p>");
        foreach ($order->getApplications() as $application) {
            $file_path = $this->applicationPdfManager->getPdfPath($application);
            $message->addAttachment('přihláška_' . $application->getId() . '.pdf', @file_get_contents($file_path));
        }
        foreach ($this->applicationPdfManager->getFilePaths($order->getEvent()) as $file) {
            $message->addAttachment($file);
        }
        $emailService->sendMessage($message);
    }

    public function getSubscribedEvents() {
        return ['OrderManager::onOrderCreated'];
    }
}