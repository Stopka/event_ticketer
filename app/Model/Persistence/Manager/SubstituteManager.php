<?php

namespace App\Model\Persistence\Manager;

use App\Model\Facades\SubstituteDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use Nette\Object;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class SubstituteManager extends Object {
    use TDoctrineEntityManager;

    /** @var  SubstituteDao */
    private $substituteDao;

    /** @var callable[]  */
    public $onSubtituteActivated = array();

    /** @var callable[]  */
    public $onSubtituteCreated = array();

    /**
     * @param SubstituteDao $substituteDao
     */
    public function injectSubstituteDao(SubstituteDao $substituteDao){
        $this->substituteDao = $substituteDao;
    }

    /**
     * @param $substituteId string|null
     */
    public function activate(?string $substituteId): void {
        $substitute = $this->substituteDao->getSubstitute($substituteId);
        if (!$substitute || in_array($substitute->getState(), [SubstituteEntity::STATE_ACTIVE, SubstituteEntity::STATE_ORDERED])) {
            return;
        }
        $substitute->setState(SubstituteEntity::STATE_ACTIVE);
        $this->getEntityManager()->flush();
        $this->onSubtituteActivated($substitute);
    }

    /**
     * @param array $values
     * @param EventEntity $event
     * @param EarlyEntity|null $early
     */
    public function createSubtituteFromForm(array $values, EventEntity $event, ?EarlyEntity $early = null): SubstituteEntity{
        $entityManager = $this->getEntityManager();
        $substitute = new SubstituteEntity();
        $substitute->setByValueArray($values);
        $substitute->setEarly($early);
        $substitute->setEvent($event);
        $entityManager->persist($substitute);
        $entityManager->flush();
        $this->onSubtituteCreated($substitute);
        return $substitute;
    }
}