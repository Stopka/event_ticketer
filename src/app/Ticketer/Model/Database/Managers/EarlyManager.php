<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Database\Daos\EarlyWaveDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;

class EarlyManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var EarlyWaveDao */
    private $earlyWaveDao;

    /** @var EarlyWaveManager */
    private $earlyWaveManager;

    /** @var callable[] */
    public $onEarlyAddedToWave = [];

    /**
     * EarlyManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param EarlyWaveDao $earlyWaveDao
     * @param EarlyWaveManager $earlyWaveManager
     */
    public function __construct(
        EntityManagerWrapper $entityManager,
        EarlyWaveDao $earlyWaveDao,
        EarlyWaveManager $earlyWaveManager
    ) {
        $this->injectEntityManager($entityManager);
        $this->earlyWaveDao = $earlyWaveDao;
        $this->earlyWaveManager = $earlyWaveManager;
    }

    /**
     * @param array<mixed> $values
     * @param EarlyEntity $earlyEntity
     * @param EventEntity $eventEntity
     * @return EarlyEntity
     * @throws \Exception
     */
    public function editEarlyFromEarlyForm(
        array $values,
        EarlyEntity $earlyEntity,
        EventEntity $eventEntity
    ): EarlyEntity {
        $em = $this->getEntityManager();
        $earlyEntity->setByValueArray($values, ['earlyWave']);
        if (!(bool)$values['earlyWaveId']) {
            $earlyWave = $this->earlyWaveManager->createWaveFromWaveForm($values['earlyWave'], $eventEntity);
        } else {
            $earlyWave = $this->earlyWaveDao->getEarlyWave($values['earlyWaveId']);
            if (null === $earlyWave) {
                throw new InvalidInputException('Wave not found');
            }
            $earlyWaveEvent = $earlyWave->getEvent();
            if (null === $earlyWaveEvent || $earlyWaveEvent->getId() !== $eventEntity->getId()) {
                throw new InvalidInputException('Wave is from different event');
            }
        }
        $earlyWaveOfEarly = $earlyEntity->getEarlyWave();
        $edited = null === $earlyWaveOfEarly || $earlyWaveOfEarly->getId() !== $earlyWave->getId();
        $earlyEntity->setEarlyWave($earlyWave);
        $em->flush();
        if ($edited) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->onEarlyAddedToWave($earlyEntity);
        }

        return $earlyEntity;
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $eventEntity
     * @return EarlyEntity
     * @throws \Exception
     */
    public function createEarlyFromEarlyForm(array $values, EventEntity $eventEntity): EarlyEntity
    {
        $em = $this->getEntityManager();
        $earlyEntity = new EarlyEntity();
        $em->persist($earlyEntity);

        return $this->editEarlyFromEarlyForm($values, $earlyEntity, $eventEntity);
    }
}
