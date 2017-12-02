<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class AdditionManager extends Object {
    use TDoctrineEntityManager;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * EventManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, CurrencyDao $currencyDao) {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param array $values
     * @param AdditionEntity $additionEntity
     * @return AdditionEntity
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
}