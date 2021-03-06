<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Database\Enums\EventStateEnum;
use Ticketer\Model\Exceptions\AlreadyDoneException;
use Ticketer\Model\Exceptions\NotFoundException;
use Ticketer\Model\Exceptions\NotReadyException;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;

class EventManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  ApplicationDao */
    private $applicationDao;

    /**
     * EventManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param ApplicationDao $applicationDao
     */
    public function __construct(EntityManagerWrapper $entityManager, ApplicationDao $applicationDao)
    {
        $this->injectEntityManager($entityManager);
        $this->applicationDao = $applicationDao;
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $eventEntity
     * @return EventEntity
     * @throws \Exception
     */
    public function editEventFromEventForm(array $values, EventEntity $eventEntity): EventEntity
    {
        $em = $this->getEntityManager();
        $eventEntity->setByValueArray($values);
        $em->flush();

        return $eventEntity;
    }

    /**
     * @param array<mixed> $values
     * @return EventEntity
     * @throws \Exception
     */
    public function createEventFromEventForm(array $values): EventEntity
    {
        $em = $this->getEntityManager();
        $eventEntity = new EventEntity();
        $eventEntity->setByValueArray($values);
        $em->persist($eventEntity);
        $em->flush();

        return $eventEntity;
    }

    /**
     * @param EventEntity|null $eventEntity
     * @param EventStateEnum|null $state
     */
    public function setEventState(?EventEntity $eventEntity, ?EventStateEnum $state = null): void
    {
        if (null === $state) {
            $state = EventStateEnum::ACTIVE();
        }
        if (null === $eventEntity) {
            throw new NotFoundException("Error.Event.NotFound");
        }
        if ($eventEntity->isActive() && EventStateEnum::ACTIVE()->equals($state)) {
            throw new AlreadyDoneException("Error.Event.AlreadyActivated");
        }
        if (EventStateEnum::CANCELLED()->equals($eventEntity->getState())) {
            throw new NotReadyException("Error.Event.AlreadyCancelled");
        }

        if (EventStateEnum::CLOSED()->equals($eventEntity->getState())) {
            throw new NotReadyException("Error.Event.AlreadyClosed");
        }
        $eventEntity->setState($state);
        $this->getEntityManager()->flush();
    }
}
