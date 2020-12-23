<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ticketer\Model\CronService;
use Ticketer\Model\Database\Daos\SubstituteDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\EntityManager;
use Nette\SmartObject;

class SubstituteManager implements EventSubscriberInterface
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  SubstituteDao */
    private $substituteDao;

    /** @var callable[] */
    public $onSubstituteActivated = [];

    /** @var callable[] */
    public $onSubstituteCreated = [];

    /**
     * SubstituteManager constructor.
     * @param EntityManager $entityManager
     * @param SubstituteDao $substituteDao
     */
    public function __construct(EntityManager $entityManager, SubstituteDao $substituteDao)
    {
        $this->injectEntityManager($entityManager);
        $this->substituteDao = $substituteDao;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CronService::class . '::onCronRun',
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
        if (null === $substitute || !in_array($substitute->getState(), SubstituteEntity::getActivableStates(), true)) {
            return;
        }
        $substitute->activate(new \DateInterval('P7D'));
        $this->getEntityManager()->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onSubstituteActivated($substitute);
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
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onSubstituteCreated($substitute);

        return $substitute;
    }
}
