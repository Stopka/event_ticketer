<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Exception\AlreadyDoneException;
use App\Model\Exception\NotFoundException;
use App\Model\Exception\NotReadyException;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Nette\SmartObject;

class EventManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  ApplicationDao */
    private $applicationDao;

    /**
     * EventManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param ApplicationDao $applicationDao
     */
    public function __construct(EntityManagerWrapper $entityManager, ApplicationDao $applicationDao) {
        $this->injectEntityManager($entityManager);
        $this->applicationDao = $applicationDao;
    }

    /**
     * @param array $values
     * @param EventEntity $eventEntity
     * @return EventEntity
     * @throws \Exception
     */
    public function editEventFromEventForm(array $values, EventEntity $eventEntity):EventEntity{
        $em = $this->getEntityManager();
        $eventEntity->setByValueArray($values);
        $em->flush();
        return $eventEntity;
    }

    /**
     * @param array $values
     * @return EventEntity
     * @throws \Exception
     */
    public function createEventFromEventForm(array $values):EventEntity{
        $em = $this->getEntityManager();
        $eventEntity = new EventEntity();
        $eventEntity->setByValueArray($values);
        $em->persist($eventEntity);
        $em->flush();
        return $eventEntity;
    }

    /**
     * @param EventEntity|null $eventEntity
     * @param int $state
     */
    public function setEventState(?EventEntity $eventEntity, int $state = EventEntity::STATE_ACTIVE): void {
        if(!$eventEntity){
            throw new NotFoundException("Error.Event.NotFound");
        }
        if($eventEntity->isActive() && $state === EventEntity::STATE_ACTIVE){
            throw new AlreadyDoneException("Error.Event.AlreadyActivated");
        }
        if($eventEntity->getState() === EventEntity::STATE_CANCELLED){
            throw new NotReadyException("Error.Event.AlreadyCancelled");
        }

        if($eventEntity->getState() === EventEntity::STATE_CLOSED){
            throw new NotReadyException("Error.Event.AlreadyClosed");
        }
        $eventEntity->setState($state);
        $this->getEntityManager()->flush();
    }
}