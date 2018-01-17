<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;

use App\Model\CronService;
use App\Model\Exception\AlreadyDoneException;
use App\Model\Exception\NotFoundException;
use App\Model\Exception\NotReadyException;
use App\Model\Persistence\Dao\EarlyWaveDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use App\Model\Persistence\Manager\EarlyWaveManager;
use Kdyby\Events\Subscriber;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\SendException;
use Nette\SmartObject;

class EarlyWaveInviteNotifier implements Subscriber {
    use SmartObject, TDoctrineEntityManager, TEmailService;

    /** @var  EarlyWaveDao */
    private $earlyWaveDao;

    /** @var callable[] */
    public $onEarlyWaveInvitesSent= Array();

    /**
     * EarlyWaveInviteNotifier constructor.
     * @param EmailService $emailService
     * @param EarlyWaveDao $earlyWaveDao
     */
    public function __construct(EmailService $emailService, EarlyWaveDao $earlyWaveDao) {
        $this->earlyWaveDao = $earlyWaveDao;
        $this->injectEmailService($emailService);
    }

    /**
     * event callback
     * @throws InvalidLinkException
     */
    public function onCronRun(){
        $this->sendUnsentInvites();
    }

    /**
     * @throws SendException
     * @throws InvalidLinkException
     */
    public function sendUnsentInvites(): void {
        $waves = $this->earlyWaveDao->getUnsentInviteEarlyWaves();
        foreach ($waves as $wave) {
            $this->sendEarlyWaveInvites($wave->getId());
        }
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function onEarlyWaveCreated(EarlyWaveEntity $earlyWave): void{
        try {
            $this->sendEarlyWaveInvites($earlyWave->getId());
        }catch(NotReadyException $exception){

        }
    }

    /**
     * @param string $waveId
     * @throws NotFoundException
     * @throws AlreadyDoneException
     * @throws NotReadyException
     * @throws SendException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function sendEarlyWaveInvites(string $waveId): void {
        $wave = $this->earlyWaveDao->getEarlyWave($waveId);
        if (!$wave) {
            throw new NotFoundException('EarlyWave not found!');
        }
        if ($wave->isInviteSent()) {
            throw new AlreadyDoneException('EarlyWave invite already sent!');
        }
        if (!$wave->isReadyToRegister()) {
            throw new NotReadyException('EarlyWave is not ready to start!');
        }
        foreach ($wave->getEarlies() as $early) {
            try {
                $this->sendEarlyInvite($early);
            } catch (NotReadyException $e) {
                continue;
            }
        }
        $this->onEarlyWaveInvitesSent($wave);
    }

    /**
     * @param EarlyEntity $early
     * @throws NotReadyException
     * @throws SendException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function sendEarlyInvite(EarlyEntity $early): void {
        if (!$early->getEmail()) {
            throw new NotReadyException("Early has no email address");
        }
        $wave = $early->getEarlyWave();
        $emailService = $this->getEmailService();
        $link = $emailService->generateLink('Front:Early:', ['id' => $early->getId()]);
        $message = $emailService->createMessage();
        $message->addTo($early->getEmail())
            ->setSubject('Přednostní výdej přihlášek')
            ->setHtmlBody("<p>Dobrý den,<br/>
Velice si vážíme Vaší podpory v minulém roce, a proto bychom Vám jako poděkování rádi nabídli odměnu v podobě přednostního výdeje přihlášek. Běžný výdej přihlášek započne " . $wave->getEvent()->getStartDate()->format('d. m. Y') . ", pro Vás ale máme přihlášky připravené již nyní. Stačí zavítat na níže uvedenou adresu, kde standardním způsobem vyplníte rezervační formulář.<br/>
$link</p>
<p>Tým LDTPardubice</p>");
        $emailService->sendMessage($message);
    }

    function getSubscribedEvents() {
        return [
            CronService::class . "::onCronRun",
            EarlyWaveManager::class . '::onEarlyWaveCreated'
        ];
    }
}