<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;

class AdditionManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * AdditionManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param CurrencyDao $currencyDao
     */
    public function __construct(EntityManagerWrapper $entityManager, CurrencyDao $currencyDao)
    {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param array<mixed> $values
     * @param AdditionEntity $additionEntity
     * @return AdditionEntity
     * @throws \Exception
     */
    public function editAdditionFromEventForm(array $values, AdditionEntity $additionEntity): AdditionEntity
    {
        $em = $this->getEntityManager();
        $additionEntity->setByValueArray($values);
        $em->flush();

        return $additionEntity;
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $eventEntity
     * @return AdditionEntity
     * @throws \Exception
     */
    public function createAdditionFromEventForm(array $values, EventEntity $eventEntity): AdditionEntity
    {
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
    public function moveAdditionUp(AdditionEntity $additionEntity): void
    {
        $event = $additionEntity->getEvent();
        if (null === $event) {
            return;
        }
        $additions = $event->getAdditions();
        $sorter = new PositionSorter();
        $sorter->moveEntityUp($additionEntity, $additions);
        $this->getEntityManager()->flush();
    }

    /**
     * @param AdditionEntity $additionEntity
     * @throws \Exception
     */
    public function moveAdditionDown(AdditionEntity $additionEntity): void
    {
        $event = $additionEntity->getEvent();
        if (null === $event) {
            return;
        }
        $sorter = new PositionSorter();
        $sorter->moveEntityDown($additionEntity, $event->getAdditions());
        $this->getEntityManager()->flush();
    }

    /**
     * @param AdditionEntity $additionEntity
     */
    public function deleteAddition(AdditionEntity $additionEntity): void
    {
        $em = $this->getEntityManager();
        $em->remove($additionEntity);
        $em->flush();
    }
}
