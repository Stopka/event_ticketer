<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;
use Tracy\Debugger;

class AdditionManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * AdditionManager constructor.
     * @param EntityManager $entityManager
     * @param CurrencyDao $currencyDao
     */
    public function __construct(EntityManager $entityManager, CurrencyDao $currencyDao) {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param array $values
     * @param AdditionEntity $additionEntity
     * @return AdditionEntity
     * @throws \Exception
     */
    public function editAdditionFromEventForm(array $values, AdditionEntity $additionEntity): AdditionEntity {
        $em = $this->getEntityManager();
        $additionEntity->setByValueArray($values);
        $em->flush();
        return $additionEntity;
    }

    /**
     * @param array $values
     * @param EventEntity $eventEntity
     * @return AdditionEntity
     * @throws \Exception
     */
    public function createAdditionFromEventForm(array $values, EventEntity $eventEntity): AdditionEntity {
        $em = $this->getEntityManager();
        $additionEntity = new AdditionEntity();
        $additionEntity->setEvent($eventEntity);
        $additionEntity->setByValueArray($values);
        $em->persist($additionEntity);
        $em->flush();
        return $additionEntity;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @throws \Exception
     */
    public function moveAdditionUp(AdditionEntity $additionEntity){
        $event = $additionEntity->getEvent();
        $additions = $event->getAdditions();
        $sorter = new PositionSorter();
        $sorter->moveEntityUp($additionEntity,$additions);
        Debugger::barDump($additions);
        $this->getEntityManager()->flush();
    }

    /**
     * @param AdditionEntity $additionEntity
     * @throws \Exception
     */
    public function moveAdditionDown(AdditionEntity $additionEntity){
        $event = $additionEntity->getEvent();
        $sorter = new PositionSorter();
        $sorter->moveEntityDown($additionEntity,$event->getAdditions());
        $this->getEntityManager()->flush();
    }
}