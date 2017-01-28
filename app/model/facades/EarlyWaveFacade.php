<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\EmailMessageFactory;
use App\Model\Entities\EarlyWaveEntity;
use App\Model\Entities\EventEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\ApplicationException;
use Nette\Mail\SendmailMailer;
use Tracy\Debugger;

class EarlyWaveFacade extends EntityFacade {

    /** @var EmailMessageFactory */
    private $emailMessageFactory;

    public function __construct(EntityManager $entityManager, EmailMessageFactory $emailMessageFactory) {
        parent::__construct($entityManager);
        $this->emailMessageFactory = $emailMessageFactory;
    }


    protected function getEntityClass() {
        return EarlyWaveEntity::class;
    }

    public function sendUnsentInvites() {
        /** @var EarlyWaveEntity[] $waves */
        $waves = $this->getRepository()->findBy([
            'event.state' => EventEntity::STATE_ACTIVE,
            'startDate <=' => new \DateTime(),
            'inviteSent' => false
        ]);
        foreach ($waves as $wave){
            $this->sendEmails($wave->getId());
        }
    }

    /**
     * @param integer $waveId
     */
    public function sendEmails($waveId) {
        /** @var EarlyWaveEntity $wave */
        $wave = $this->get($waveId);
        if ($wave->isInviteSent()) {
            throw new ApplicationException('EarlyWave invite already sent!');
        }
        if (!$wave->isReadyToRegister()) {
            throw new ApplicationException('EarlyWave is not ready to start!');
        }
        foreach ($wave->getEarlies() as $early) {
            if (!$early->getEmail()) {
                continue;
            }
            $link = $this->emailMessageFactory->link('Front:Early:',['id'=>$early->getHashId()]);
            $mail = $this->emailMessageFactory->create();
            $mail->addTo($early->getEmail())
                ->setSubject('Přednostní výdej přihlášek')
                ->setHtmlBody("<p>Dobrý den,<br/>
Velice si vážíme Vaší podpory v minulém roce, a proto bychom Vám jako poděkování rádi nabídli odměnu v podobě přednostního výdeje přihlášek. Běžný výdej přihlášek započne " . $wave->getEvent()->getStartDate()->format('d. m. Y') . ", pro Vás ale máme přihlášky připravené již nyní. Stačí zavítat na níže uvedenou adresu, kde standardním způsobem vyplníte rezervační formulář.<br/>
$link</p>
<p>Tým LDTPardubice</p>");
            $mailer = new SendmailMailer();
            Debugger::barDump($mail);
            $mailer->send($mail);
        }
        $wave->setInviteSent();
        $this->getEntityManager()->flush();
    }

}