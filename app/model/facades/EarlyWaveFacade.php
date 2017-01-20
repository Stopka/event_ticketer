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
use Kdyby\Doctrine\EntityManager;
use Nette\Application\ApplicationException;
use Nette\Mail\SmtpMailer;
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

    /**
     * @param integer $wave_id
     */
    public function sendEmails($wave_id){
        /** @var EarlyWaveEntity $wave */
        $wave = $this->get($wave_id);
        if($wave->isReadyToRegister()){
            throw new ApplicationException('EarlyWave is not ready to start!');
        }
        foreach ($wave->getEarlies() as $early){
            if(!$early->getEmail()){
                continue;
            }
            $mail = $this->emailMessageFactory->create();
            $mail->setFrom('system@ldtpardubice.cz','Přihlášky LDTPardubice')
                ->addReplyTo('ldtmpp@email.cz')
                ->addTo($early->getEmail())
                ->setSubject('Přednostní výdej přihlášek')
                ->setBody("Dobrý den,
Velice si vážíme Vaší podpory v minulém roce, a proto bychom Vám jako poděkování rádi nabídli odměnu v podobě přednostního výdeje přihlášek. Běžný výdej přihlášek započne ".$wave->getEvent()->getStartDate()->format('d. m. Y').", pro Vás ale máme přihlášky připravené již nyní. Stačí zavítat na níže uvedenou adresu, kde standardním způsobem vyplníte rezervační formulář.
http://application.ldtpardubice.cz/early/".$early->getId()."_".$early->getGuid()."
Tým LDTPardubice");
            $mailer = new SmtpMailer();
            Debugger::barDump($mail);
            //TODO odkomentovat
            //$mailer->send($mail);
        }
    }

}