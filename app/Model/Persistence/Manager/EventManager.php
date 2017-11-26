<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OrderEntity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\Object;

class EventManager extends Object implements Subscriber {
    use TDoctrineEntityManager;

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
     * @param OrderEntity $orderEntity
     */
    public function onOrderCreated(OrderEntity $orderEntity) {
        $event = $orderEntity->getEvent();
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
        return ['OrderManager::onOrderCreated'];
    }
}