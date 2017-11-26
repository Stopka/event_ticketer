<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 19:13
 */

namespace App\Model\Persistence\Factory;

use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use Kdyby\Events\Subscriber;
use Nette\Object;

class EarlyWaveManager extends Object implements Subscriber {
    use TDoctrineEntityManager;

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