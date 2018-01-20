<?php

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\SubstituteDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class SubstituteManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  SubstituteDao */
    private $substituteDao;

    /** @var callable[]  */
    public $onSubtituteActivated = array();

    /** @var callable[]  */
    public $onSubtituteCreated = array();

    /**
     * SubstituteManager constructor.
     * @param EntityManager $entityManager
     * @param SubstituteDao $substituteDao
     */
    public function __construct(EntityManagerWrapper $entityManager, SubstituteDao $substituteDao) {
        $this->injectEntityManager($entityManager);
        $this->substituteDao = $substituteDao;
    }


    /**
     * @param null|string $substituteId
     * @throws \Exception
     */
    public function activateSubstitute(?string $substituteId): void {
        $substitute = $this->substituteDao->getSubstitute($substituteId);
        if (!$substitute || in_array($substitute->getState(), [SubstituteEntity::STATE_ACTIVE, SubstituteEntity::STATE_ORDERED])) {
            return;
        }
        $substitute->setState(SubstituteEntity::STATE_ACTIVE);
        $this->getEntityManager()->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onSubtituteActivated($substitute);
    }

    /**
     * @param array $values
     * @param EventEntity $event
     * @param EarlyEntity|null $early
     * @return SubstituteEntity
     * @throws \Exception
     */
    public function createSubtituteFromForm(array $values, EventEntity $event, ?EarlyEntity $early = null): SubstituteEntity{
        $entityManager = $this->getEntityManager();
        $substitute = new SubstituteEntity();
        $substitute->setByValueArray($values);
        $substitute->setEarly($early);
        $substitute->setEvent($event);
        $entityManager->persist($substitute);
        $entityManager->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onSubtituteCreated($substitute);
        return $substitute;
    }
}