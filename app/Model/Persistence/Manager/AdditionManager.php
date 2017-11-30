<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Tracy\Debugger;

class AdditionManager extends Object {
    use TDoctrineEntityManager;

    /** @var  AdditionDao */
    private $additionDao;

    /**
     * EventManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->injectEntityManager($entityManager);
    }

    public function editAdditionFromEventForm(array $values, AdditionEntity $additionEntity):AdditionEntity{
        $em = $this->getEntityManager();
        $additionEntity->setByValueArray($values);
        $em->flush();
        return $additionEntity;
    }

    public function createAdditionFromEventForm(array $values):AdditionEntity{
        $em = $this->getEntityManager();
        Debugger::barDump($values);
        $additionEntity = new AdditionEntity();
        /*$additionEntity->setByValueArray($values);
        $em->persist($additionEntity);
        $em->flush();
        */

        return $additionEntity;
    }
}