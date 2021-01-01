<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Cron\HourCronEvent;
use Ticketer\Model\Database\Managers\Events\EarlyAddedToWaveEvent;
use Ticketer\Model\Database\Managers\Events\EarlyWaveCreatedEvent;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Exceptions\AlreadyDoneException;
use Ticketer\Model\Exceptions\NotFoundException;
use Ticketer\Model\Exceptions\NotReadyException;
use Ticketer\Model\Database\Daos\EarlyWaveDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\SendException;
use Nette\SmartObject;
use Ticketer\Model\Notifiers\Events\EarlyWaveInvitesSentEvent;

class EarlyWaveInviteNotifier implements EventSubscriberInterface
{
    use SmartObject;
    use TDoctrineEntityManager;
    use TEmailService;
    use TAtachmentManager;

    /** @var  EarlyWaveDao */
    private $earlyWaveDao;

    public EventDispatcherInterface $eventDispatcher;

    /**
     * EarlyWaveInviteNotifier constructor.
     * @param EmailService $emailService
     * @param EarlyWaveDao $earlyWaveDao
     * @param IEventMessageAtachmentManagerFactory $atachmentManagerFactory
     */
    public function __construct(
        EmailService $emailService,
        EarlyWaveDao $earlyWaveDao,
        IEventMessageAtachmentManagerFactory $atachmentManagerFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->earlyWaveDao = $earlyWaveDao;
        $this->injectEmailService($emailService);
        $this->injectAtachmentManagerFactory($atachmentManagerFactory);
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getAtachmentManagerNamespace(): string
    {
        return "EarlyWaveInvite";
    }

    /**
     * event callback
     * @throws InvalidLinkException
     */
    public function onCronRun(): void
    {
        $this->sendUnsentInvites();
    }

    /**
     * @throws SendException
     * @throws InvalidLinkException
     */
    public function sendUnsentInvites(): void
    {
        $waves = $this->earlyWaveDao->getUnsentInviteEarlyWaves();
        foreach ($waves as $wave) {
            $this->sendUnsentEarlyWaveInvites($wave->getId());
        }
    }

    /**
     * @param EarlyWaveCreatedEvent $event
     * @throws InvalidLinkException
     */
    public function onEarlyWaveCreated(EarlyWaveCreatedEvent $event): void
    {
        try {
            $earlyWave = $event->getEarlyWave();
            $this->sendUnsentEarlyWaveInvites($earlyWave->getId());
        } catch (NotReadyException $exception) {
        }
    }

    /**
     * @param EarlyAddedToWaveEvent $event
     * @throws InvalidLinkException
     */
    public function onEarlyAddedToWave(EarlyAddedToWaveEvent $event): void
    {
        $earlyEntity = $event->getEarly();
        $earlyWave = $earlyEntity->getEarlyWave();
        if (null === $earlyWave) {
            return;
        }
        if (!$earlyWave->isInviteSent()) {
            return;
        }
        $this->sendEarlyInvite($earlyEntity);
    }

    /**
     * @param Uuid $waveId
     * @throws InvalidLinkException
     */
    public function sendUnsentEarlyWaveInvites(Uuid $waveId): void
    {
        $wave = $this->earlyWaveDao->getEarlyWave($waveId);
        if (null === $wave) {
            throw new NotFoundException('EarlyWave not found!');
        }
        if ($wave->isInviteSent()) {
            throw new AlreadyDoneException('EarlyWave invite already sent!');
        }
        if (!$wave->isReadyToRegister()) {
            throw new NotReadyException('EarlyWave is not ready to start!');
        }
        $this->sendEarlyWaveInvites($wave);
        $this->eventDispatcher->dispatch(new EarlyWaveInvitesSentEvent($wave));
    }

    /**
     * @param EarlyWaveEntity $wave
     * @throws InvalidLinkException
     */
    private function sendEarlyWaveInvites(EarlyWaveEntity $wave): void
    {
        foreach ($wave->getEarlies() as $early) {
            try {
                $this->sendEarlyInvite($early);
            } catch (NotReadyException $e) {
                continue;
            }
        }
    }

    /**
     * @param Uuid $waveId
     */
    public function sendDebugEarlyWaveInvites(Uuid $waveId): void
    {
        $wave = $this->earlyWaveDao->getEarlyWave($waveId);
        if (null === $wave) {
            return;
        }
        $this->sendEarlyWaveInvites($wave);
    }

    /**
     * @param EarlyEntity $early
     * @throws NotReadyException
     * @throws SendException
     * @throws InvalidLinkException
     */
    protected function sendEarlyInvite(EarlyEntity $early): void
    {
        $emailAddress = $early->getEmail();
        if (null === $emailAddress) {
            throw new NotReadyException("Early has no email address");
        }
        $wave = $early->getEarlyWave();
        if (null === $wave) {
            throw new NotReadyException("Early has no wave");
        }
        $event = $wave->getEvent();
        if (null === $event) {
            throw new NotReadyException("Early has no event");
        }
        $startDate = $event->getStartDate();
        if (null === $startDate) {
            throw new NotReadyException("Event has no start date");
        }
        $emailService = $this->getEmailService();
        $link = $emailService->generateLink('Front:Early:', ['id' => $early->getId()]);
        $message = $emailService->createMessage();
        $message->addTo($emailAddress)
            ->setSubject('Přednostní výdej přihlášek')
            ->setHtmlBody(
                "<p>Dobrý den,<br/>
Velice si vážíme Vaší podpory v minulém roce,
a proto bychom Vám jako poděkování rádi nabídli odměnu v podobě přednostního výdeje přihlášek na
<a href='https://ldtpardubice.cz/event/612'>" . $event->getName() . "</a>.
Běžný výdej přihlášek započne " . $startDate->format('d. m. Y') . ",
pro Vás ale máme přihlášky připravené již nyní. Stačí zavítat na níže uvedenou adresu,
kde standardním způsobem vyplníte registrační formulář. Učinit tak můžete do 25. 1. 2019.<br/>
$link</p>
<p>Tábor se bude konat v termínu <strong>21. 7. - 4. 8. 2019</strong> v areálu
<a href='https://ldtpardubice.cz/event_place/havlisuv_mlyn_u_zeletavy'>Havlišův mlýn u Želetavy</a>.</p>
<p>Zároveň bychom Vás při této příležitosti chtěli pozvat na
<a href='https://ldtpardubice.cz/event/611'>Sváteční setkání</a>, které proběhne dne 15. 12. 2018 v budově
<a href='https://ldtpardubice.cz/event_place/sdh_slovany'>SDH Slovany</a>,
ul. Krátká 673, Pardubice (informace k akci i na
<a href = 'https://www.facebook.com/events/186098655600221/'>facebooku</a>). Na tomto setkání proběhne
<strong>křest</strong> našeho nového <strong>maskota</strong>
a rodiče budou mít možnost zajistit přihlášky pro své příbuzné a kamarády. Vše v krásné
<strong>vánoční atmosféře</strong> s punčem, cukrovím, stromečkem, za znění tradičních koled.</p>
<p>Tým LDTPardubice</p>

<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz
na základě otevření přednostního přístupu k přihláškám.</em></p>"
            );
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
            HourCronEvent::class => 'onCronRun',
            EarlyWaveCreatedEvent::class => 'onEarlyWaveCreated',
            EarlyAddedToWaveEvent::class => "onEarlyAddedToWave",
        ];
    }
}