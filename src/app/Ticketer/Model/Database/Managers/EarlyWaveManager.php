<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Database\Managers\Events\EarlyWaveCreatedEvent;
use Ticketer\Model\Notifiers\EarlyWaveInviteNotifier;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;
use Ticketer\Model\Notifiers\Events\EarlyWaveInvitesSentEvent;

class EarlyWaveManager implements EventSubscriberInterface
{
    use SmartObject;
    use TDoctrineEntityManager;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * EarlyWaveManager constructor.
     * @param EntityManagerWrapper $entityManager
     */
    public function __construct(EntityManagerWrapper $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->injectEntityManager($entityManager);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param EarlyWaveInvitesSentEvent $event
     * @throws \Exception
     */
    public function onEarlyWaveInvitesSent(EarlyWaveInvitesSentEvent $event): void
    {
        $this->setEarlyWaveInvitesSent($event->getEarlyWave());
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
        return [
            EarlyWaveInvitesSentEvent::class => 'onEarlyWaveInvitesSent',
        ];
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
        $this->eventDispatcher->dispatch(new EarlyWaveCreatedEvent($earlyWaveEntity));

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
