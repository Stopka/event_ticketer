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
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\SmartObject;

class EventManager implements Subscriber {
    use SmartObject, TDoctrineEntityManager;

    /** @var  ApplicationDao */
    private $applicationDao;

    /**
     * EventManager constructor.
     * @param EntityManager $entityManager
     * @param ApplicationDao $applicationDao
     */
    public function __construct(EntityManager $entityManager, ApplicationDao $applicationDao) {
        $this->injectEntityManager($entityManager);
        $this->applicationDao = $applicationDao;
    }

    /**
     * Event callback
     * @param CartEntity $cartEntity
     */
    public function onCartCreated(CartEntity $cartEntity) {
        $event = $cartEntity->getEvent();
        if (!$event) {
            return;
        }
        $this->updateEventCapacityFull($event);
    }

    public function updateEventCapacityFull(EventEntity $event): void {
        $isFull = $event->isCapacityFull($this->applicationDao->countIssuedApplications($event));
        $event->setCapacityFull($isFull);
        $this->getEntityManager()->flush();
    }

    public function getSubscribedEvents() {
        return ['CartManager::onCartCreated'];
    }

    public function editEventFromEventForm(array $values, EventEntity $eventEntity):EventEntity{
        $em = $this->getEntityManager();
        $eventEntity->setByValueArray($values);
        $em->flush();
        return $eventEntity;
    }

    public function createEventFromEventForm(array $values):EventEntity{
        $em = $this->getEntityManager();
        $eventEntity = new EventEntity();
        $eventEntity->setByValueArray($values);
        $em->persist($eventEntity);
        $em->flush();
        return $eventEntity;
    }

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