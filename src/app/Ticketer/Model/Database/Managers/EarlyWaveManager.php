<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Notifiers\EarlyWaveInviteNotifier;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;

class EarlyWaveManager implements EventSubscriberInterface
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var callable[] */
    public $onEarlyWaveCreated = [];

    /**
     * EarlyWaveManager constructor.
     * @param EntityManagerWrapper $entityManager
     */
    public function __construct(EntityManagerWrapper $entityManager)
    {
        $this->injectEntityManager($entityManager);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @throws \Exception
     */
    public function onEarlyWaveInvitesSent(EarlyWaveEntity $earlyWave): void
    {
        $this->setEarlyWaveInvitesSent($earlyWave);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @throws \Exception
     */
    public function setEarlyWaveInvitesSent(EarlyWaveEntity $earlyWave): void
    {
        $earlyWave->setInviteSent();
        $this->getEntityManager()->flush();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [EarlyWaveInviteNotifier::class . '::onEarlyWaveInvitesSent'];
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $eventEntity
     * @return EarlyWaveEntity
     */
    public function createWaveFromWaveForm(array $values, EventEntity $eventEntity): EarlyWaveEntity
    {
        $em = $this->getEntityManager();
        $earlyWaveEntity = new EarlyWaveEntity();
        $earlyWaveEntity->setEvent($eventEntity);
        $earlyWaveEntity->setByValueArray($values);
        $em->persist($earlyWaveEntity);
        $em->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onEarlyWaveCreated($earlyWaveEntity);

        return $earlyWaveEntity;
    }

    /**
     * @param array<mixed> $values
     * @param EarlyWaveEntity $earlyWaveEntity
     * @return EarlyWaveEntity
     */
    public function editWaveFromWaveForm(array $values, EarlyWaveEntity $earlyWaveEntity): EarlyWaveEntity
    {
        $earlyWaveEntity->setByValueArray($values);
        $this->getEntityManager()->flush();

        return $earlyWaveEntity;
    }
}
