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
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\SmartObject;

class EarlyWaveManager implements Subscriber {
    use SmartObject, TDoctrineEntityManager;

    /** @var callable[] */
    public $onEarlyWaveCreated = Array();

    /**
     * EarlyWaveManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->injectEntityManager($entityManager);
    }

    public function onEarlyWaveInvitesSent(EarlyWaveEntity $earlyWave): void {
        $this->setEarlyWaveInvitesSent($earlyWave);
    }

    public function setEarlyWaveInvitesSent(EarlyWaveEntity $earlyWave): void {
        $earlyWave->setInviteSent();
        $this->getEntityManager()->flush();
    }

    function getSubscribedEvents() {
        return ['EarlyWaveInviteNotifier::onEarlyWaveInvitesSent'];
    }

    /**
     * @param array $values
     * @param EventEntity $eventEntity
     * @return EarlyWaveEntity
     */
    public function createWaveFromWaveForm(array $values, EventEntity $eventEntity): EarlyWaveEntity {
        $em = $this->getEntityManager();
        $earlyWaveEntity = new EarlyWaveEntity();
        $earlyWaveEntity->setEvent($eventEntity);
        $earlyWaveEntity->setByValueArray($values);
        $em->persist($earlyWaveEntity);
        $em->flush();
        $this->onEarlyWaveCreated();
        return $earlyWaveEntity;
    }
}