<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 19:13
 */

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\SmartObject;

class EarlyWaveManager implements Subscriber {
    use SmartObject, TDoctrineEntityManager;

    /**
     * EarlyWaveManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->injectEntityManager($entityManager);
    }

    public function onEarlyWaveInvitesSent(EarlyWaveEntity $earlyWave): void{
        $this->setEarlyWaveInvitesSent($earlyWave);
    }

    public function setEarlyWaveInvitesSent(EarlyWaveEntity $earlyWave):void{
        $earlyWave->setInviteSent();
        $this->getEntityManager()->flush();
    }

    function getSubscribedEvents() {
        return ['EarlyWaveInviteNotifier::onEarlyWaveInvitesSent'];
    }
}