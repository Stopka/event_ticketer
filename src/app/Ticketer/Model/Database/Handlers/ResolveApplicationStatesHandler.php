<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Dtos\Uuid;

class ResolveApplicationStatesHandler
{
    private EventDao $eventDao;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EventDao $eventDao,
        EntityManagerInterface $entityManager
    ) {
        $this->eventDao = $eventDao;
        $this->entityManager = $entityManager;
    }


    public function resolveApplicationStates(Uuid $eventUuid): void
    {
        $event = $this->eventDao->getEvent($eventUuid);
        if (null === $event) {
            throw new InvalidArgumentException("No event with id '{$eventUuid->toString()}'");
        }
        foreach ($event->getApplications() as $application) {
            $application->updateState();
            $this->entityManager->persist($application);
        }
        $this->entityManager->flush();
    }
}
