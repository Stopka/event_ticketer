<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\Cron\CronService;
use Ticketer\Model\Cron\HourCronEvent;
use Ticketer\Model\Database\Daos\SubstituteDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\EntityManager;
use Nette\SmartObject;
use Ticketer\Model\Database\Managers\Events\SubstituteActivatedEvent;
use Ticketer\Model\Database\Managers\Events\SubstituteCreatedEvent;

class SubstituteManager implements EventSubscriberInterface
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  SubstituteDao */
    private $substituteDao;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * SubstituteManager constructor.
     * @param EntityManager $entityManager
     * @param SubstituteDao $substituteDao
     */
    public function __construct(
        EntityManager $entityManager,
        SubstituteDao $substituteDao,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->injectEntityManager($entityManager);
        $this->substituteDao = $substituteDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            HourCronEvent::class => 'onCronRun',
        ];
    }

    public function onCronRun(): void
    {
        $substitutes = $this->substituteDao->getOverdueSubstitutesReadyToUpdateState();
        foreach ($substitutes as $substite) {
            $substite->updateState();
        }
        $this->getEntityManager()->flush();
    }


    /**
     * @param SubstituteEntity|null $substitute
     */
    public function activateSubstitute(?SubstituteEntity $substitute): void
    {
        if (null === $substitute || !$substitute->getState()->isActivable()) {
            return;
        }
        $substitute->activate(new \DateInterval('P7D'));
        $this->getEntityManager()->flush();
        $this->eventDispatcher->dispatch(new SubstituteActivatedEvent($substitute));
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $event
     * @param EarlyEntity|null $early
     * @return SubstituteEntity
     * @throws \Exception
     */
    public function createSubtituteFromForm(
        array $values,
        EventEntity $event,
        ?EarlyEntity $early = null
    ): SubstituteEntity {
        $entityManager = $this->getEntityManager();
        $substitute = new SubstituteEntity();
        $substitute->setByValueArray($values);
        $substitute->setEarly($early);
        $substitute->setEvent($event);
        $entityManager->persist($substitute);
        $entityManager->flush();
        $this->eventDispatcher->dispatch(new SubstituteCreatedEvent($substitute));

        return $substitute;
    }
}
