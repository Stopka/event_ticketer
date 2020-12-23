<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Controls\Forms\AdditionsControlsBuilder;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Model\Database\Daos\InsuranceCompanyDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;
use Ticketer\Model\Exceptions\InvalidInputException;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ApplicationManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var InsuranceCompanyDao */
    private $insuranceCompanyDao;

    /** @var ChoiceManager */
    private $choiceManager;

    /**
     * ApplicationManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param InsuranceCompanyDao $insuranceCompanyDao
     * @param ChoiceManager $choiceManager
     */
    public function __construct(
        EntityManagerWrapper $entityManager,
        InsuranceCompanyDao $insuranceCompanyDao,
        ChoiceManager $choiceManager
    ) {
        $this->injectEntityManager($entityManager);
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->choiceManager = $choiceManager;
    }

    /**
     * @param array<mixed> $values
     * @param array<mixed> $commonValues
     * @param EventEntity $event
     * @param ApplicationEntity|null $application
     * @param bool $reserve
     * @return ApplicationEntity
     */
    private function processApplicationForm(
        array $values,
        array $commonValues,
        EventEntity $event,
        ?ApplicationEntity $application = null,
        bool $reserve = false
    ): ApplicationEntity {
        $entityManager = $this->getEntityManager();
        $created = false;
        if (null === $application) {
            $application = new ApplicationEntity($reserve);
            $application->setEvent($event);
            //$application->setNextNumber($entityManager);
            $entityManager->persist($application);
            $created = true;
        }
        $application->setByValueArray($commonValues);
        if (isset($values[CartFormWrapper::CONTAINER_NAME_APPLICATION])) {
            $application->setByValueArray($values[CartFormWrapper::CONTAINER_NAME_APPLICATION]);
            if (isset($values[CartFormWrapper::CONTAINER_NAME_APPLICATION]['insuranceCompanyId'])) {
                $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany(
                    $values[CartFormWrapper::CONTAINER_NAME_APPLICATION]['insuranceCompanyId']
                );
                if (null !== $insuranceCompany) {
                    $application->setInsuranceCompany($insuranceCompany);
                }
            }
        }
        if ($created) {
            $this->choiceManager->createAdditionChoicesToApplication(
                $values[AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS],
                $application,
                $reserve
            );
        } else {
            $this->choiceManager->editAdditionChoicesInApplication(
                $values[AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS],
                $application,
                $reserve
            );
        }

        return $application;
    }

    /**
     * @param array<mixed> $values
     * @param array<mixed> $commonValues
     * @param EventEntity $event
     * @return ApplicationEntity
     */
    public function createApplicationFromCartForm(
        array $values,
        array $commonValues,
        EventEntity $event
    ): ApplicationEntity {
        return $this->processApplicationForm($values, $commonValues, $event);
    }

    /**
     * @param array<mixed> $values
     * @param array<mixed> $commonValues
     * @param ApplicationEntity $application
     * @return ApplicationEntity
     */
    public function editApplicationFromCartForm(
        array $values,
        array $commonValues,
        ApplicationEntity $application
    ): ApplicationEntity {
        $event = $application->getEvent();
        if (null === $event) {
            throw new InvalidInputException('Application is in no event');
        }
        $application = $this->processApplicationForm($values, $commonValues, $event, $application);

        return $application;
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $event
     * @return ApplicationEntity
     */
    public function createReservedApplicationFromReservationForm(array $values, EventEntity $event): ApplicationEntity
    {
        return $this->processApplicationForm($values, [], $event, null, true);
    }

    /**
     * @param array<mixed> $values
     * @param ApplicationEntity $applicationEntity
     */
    public function editReservedApplicationFromReservationForm(
        array $values,
        ApplicationEntity $applicationEntity
    ): void {
        $event = $applicationEntity->getEvent();
        if (null === $event) {
            throw new InvalidInputException('Application is in no event');
        }
        $this->processApplicationForm($values, [], $event, $applicationEntity, true);
    }

    public function cancelApplication(ApplicationEntity $applicationEntity): void
    {
        $applicationEntity->cancelApplication();
        $this->getEntityManager()->flush();
    }
}
