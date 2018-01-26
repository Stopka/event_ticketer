<?php

namespace App\Model\Persistence\Manager;

use App\Controls\Forms\AdditionsControlsBuilder;
use App\Controls\Forms\CartFormWrapper;
use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ApplicationManager {
    use SmartObject, TDoctrineEntityManager;

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

    private function processApplicationForm(
        array $values,
        array $commonValues = [],
        EventEntity $event,
        ?ApplicationEntity $application = null,
        bool $reserve = false
    ): ApplicationEntity {
        $entityManager = $this->getEntityManager();
        $created = false;
        if (!$application) {
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
                $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($values[CartFormWrapper::CONTAINER_NAME_APPLICATION]['insuranceCompanyId']);
                $application->setInsuranceCompany($insuranceCompany);
            }
        }
        if ($created) {
            $this->choiceManager->createAdditionChoicesToApplication($values[AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS], $application, $reserve);
        } else {
            $this->choiceManager->editAdditionChoicesInApplication($values[AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS], $application);
        }
        return $application;
    }

    /**
     * @param array $values
     * @param array $commonValues
     * @param EventEntity $event
     * @return ApplicationEntity
     */
    public function createApplicationFromCartForm(array $values, array $commonValues = [], EventEntity $event): ApplicationEntity {
        $application = $this->processApplicationForm($values, $commonValues, $event);
        return $application;
    }

    /**
     * @param array $values
     * @param array $commonValues
     * @param ApplicationEntity $application
     * @return ApplicationEntity
     */
    public function editApplicationFromCartForm(array $values, array $commonValues = [], ApplicationEntity $application): ApplicationEntity {
        $application = $this->processApplicationForm($values, $commonValues, $application->getEvent(), $application);
        return $application;
    }

    /**
     * @param array $values
     * @param EventEntity $event
     * @return ApplicationEntity
     */
    public function createReservedApplicationFromReservationForm(array $values, EventEntity $event): ApplicationEntity {
        $application = $this->processApplicationForm($values, [], $event, null, true);
        return $application;
    }

    public function editReservedApplicationFromReservationForm(array $values, ApplicationEntity $applicationEntity) {
        $this->processApplicationForm($values, [], $applicationEntity->getEvent(), $applicationEntity, true);
    }
}