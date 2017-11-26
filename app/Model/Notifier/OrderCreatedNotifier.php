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

    /** @var  EmailService */
    private $emailService;

    /** @var  ApplicationPdfManager */
    private $pdfApplicationFacade;


    /**
     * @param ApplicationPdfManager $pdfApplicationFacade
     */
    public function injectPdfApplicationFacade(ApplicationPdfManager $pdfApplicationFacade): void {
        $this->pdfApplicationFacade = $pdfApplicationFacade;
    }

    /**
     * @param EmailService $emailService
     */
    public function injectEmailService(EmailService $emailService): void {
        $this->emailService = $emailService;
    }

    /**
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
        $link = $this->emailService->generateLink('Front:Order:', ['id' => $order->getHashId()]);
        $message = $this->emailService->createMessage();
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
            $file_path = $this->pdfApplicationFacade->getPdfPath($application);
            $message->addAttachment('přihláška_' . $application->getId() . '.pdf', @file_get_contents($file_path));
        }
        foreach ($this->pdfApplicationFacade->getFilePaths($order->getEvent()) as $file) {
            $message->addAttachment($file);
        }
        $this->emailService->sendMessage($message);
    }

    public function getSubscribedEvents() {
        return ['OrderManager::onOrderCreated'];
    }
}