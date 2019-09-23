<?php

namespace App\Model\Persistence\Manager;

use App\Model\CronService;
use App\Model\Persistence\Dao\SubstituteDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Kdyby\Events\Subscriber;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class SubstituteManager implements Subscriber {
    use SmartObject, TDoctrineEntityManager;

    /** @var  SubstituteDao */
    private $substituteDao;

    /** @var callable[] */
    public $onSubstituteActivated = array();

    /** @var callable[] */
    public $onSubstituteCreated = array();

    /**
     * SubstituteManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param SubstituteDao $substituteDao
     */
    public function __construct(EntityManagerWrapper $entityManager, SubstituteDao $substituteDao) {
        $this->injectEntityManager($entityManager);
        $this->substituteDao = $substituteDao;
    }

    function getSubscribedEvents() {
        return [
            CronService::class . '::onCronRun'
        ];
    }

    public function onCronRun() {
        $substites = $this->substituteDao->getOverdueSubstitutesReadyToUpdateState();
        foreach ($substites as $substite) {
            $substite->updateState();
        }
        $this->getEntityManager()->flush();
    }


    /**
     * @param null|string $substituteId
     * @throws \Exception
     */
    public function activateSubstitute(SubstituteEntity $substitute): void {
        if (!$substitute || !in_array($substitute->getState(), SubstituteEntity::getActivableStates())) {
            return;
        }
        $substitute->activate(new \DateInterval('P7D'));
        $this->getEntityManager()->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onSubstituteActivated($substitute);
    }

    /**
     * @param array $values
     * @param EventEntity $event
     * @param EarlyEntity|null $early
     * @return SubstituteEntity
     * @throws \Exception
     */
    public function createSubtituteFromForm(array $values, EventEntity $event, ?EarlyEntity $early = null): SubstituteEntity {
        $entityManager = $this->getEntityManager();
        $substitute = new SubstituteEntity();
        $substitute->setByValueArray($values);
        $substitute->setEarly($early);
        $substitute->setEvent($event);
        $entityManager->persist($substitute);
        $entityManager->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onSubstituteCreated($substitute);
        return $substitute;
    }
}