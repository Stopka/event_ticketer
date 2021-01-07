<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Nette\Application\UI\InvalidLinkException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Database\Managers\Events\CartCreatedEvent;
use Ticketer\Model\Exceptions\NotReadyException;
use Ticketer\Model\IApplicationPdfManager;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Managers\CartManager;
use Nette\Mail\SendException;
use Nette\SmartObject;

class CartCreatedNotifier implements EventSubscriberInterface
{
    use SmartObject;
    use TEmailService;
    use TAtachmentManager;

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

    protected function getAtachmentManagerNamespace(): string
    {
        return "CartCreated";
    }


    /**
     * Event callback
     * @param CartCreatedEvent $event
     * @throws InvalidLinkException
     */
    public function onCartCreated(CartCreatedEvent $event): void
    {
        $this->sendNotification($event->getCart());
    }

    /**
     * @param CartEntity $cartEntity
     * @throws NotReadyException
     * @throws SendException
     * @throws InvalidLinkException
     */
    public function sendNotification(CartEntity $cartEntity): void
    {
        $emailAddress = $cartEntity->getEmail();
        if (null === $emailAddress) {
            throw new NotReadyException("Cart has no email address");
        }
        $event = $cartEntity->getEvent();
        if (null === $event) {
            throw new NotReadyException("Cart has no event");
        }
        $emailService = $this->getEmailService();
        $link = $emailService->generateLink('Front:Cart:', ['id' => $cartEntity->getId()->toString()]);
        $message = $emailService->createMessage();
        $message->addTo($emailAddress, $cartEntity->getFullName());
        $message->setSubject('Přihláška na ' . $event->getName());
        $message->setHtmlBody(
            "<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na
<a href='https://ldtpardubice.cz/event/612'>" . $event->getName() . "</a>.
V příloze zasíláme přihlášku, prohlášení o bezinfekčnosti, posudek o zdravotni způsobilosti
a list se základními informacemi. Bezinfekčnost, lékařský posudek
a list s informacemi můžete v případě ztráty získat na našich
<a href='https://ldtpardubice.cz/article/613'>stránkách</a>.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé registrované místo, dovyplnit,
odeslat a ke každé přihlášce zaplatit rezervační poplatek.
Další informace jsou uvedeny přímo v přiloženém dokumentu popřípadě na našich stránkách.</p>
<p>Aktuální stav Vašich přihlášek můžete průběžně sledovat na následující adrese: <br />
 <a href='$link'>$link</a></p>
<p>V případě dotazu pište na <a href='mailto:ldtmpp@email.cz'>ldtmpp@email.cz</a>.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz
na základě registrace přihlášky.</em></p>"
        );
        foreach ($cartEntity->getApplications() as $application) {
            $this->applicationPdfManager->addMessageAttachment($message, $application);
        }
        $atachmentManager = $this->getAtachmentManager($event);
        $atachmentManager->addAttachmentsToMessage($message);
        $emailService->sendMessage($message);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CartCreatedEvent::class => 'onCartCreated',
        ];
    }
}
