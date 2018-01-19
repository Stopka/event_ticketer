<?php

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Doctrine\EntityManager;
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

    /** @var ReservationManager */
    private $reservationManager;

    /**
     * ApplicationManager constructor.
     * @param EntityManager $entityManager
     * @param InsuranceCompanyDao $insuranceCompanyDao
     * @param ChoiceManager $choiceManager
     * @param ReservationManager $reservationManager
     */
    public function __construct(
        EntityManager $entityManager,
        InsuranceCompanyDao $insuranceCompanyDao,
        ChoiceManager $choiceManager,
        ReservationManager $reservationManager
    ) {
        $this->injectEntityManager($entityManager);
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->choiceManager = $choiceManager;
        $this->reservationManager = $reservationManager;
    }

    public function createApplicationFromCartForm(array $values, array $commonValues = [], EventEntity $event): ApplicationEntity {
        $entityManager = $this->getEntityManager();
        $application = new ApplicationEntity();
        $application->setEvent($event);
        $application->setByValueArray($commonValues);
        $application->setByValueArray($values['child']);
        $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($values['insuranceCompanyId']);
        $application->setInsuranceCompany($insuranceCompany);
        $application->setNextNumber($entityManager);
        $entityManager->persist($application);
        $this->choiceManager->createAdditionChoicesToApplication($values['additions'], $application);
        return $application;
    }

    public function editApplicationFromCartForm(array $values, array $commonValues = [], ApplicationEntity $application): ApplicationEntity {
        //update existing application data
        $application->setByValueArray($commonValues);
        $application->setByValueArray($values['child']);
        $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($values['insuranceCompanyId']);
        $application->setInsuranceCompany($insuranceCompany);
        //$entityManager->persist($application);
        $this->choiceManager->editAdditionChoicesInApplication($values['addittions'], $application);
        return $application;
    }

    /**
     * @param array $values
     * @param EventEntity|null $event
     * @throws \Exception
     */
    public function createReservedApplicationsFromReservationForm(array $values, EventEntity $event): void {
        $entityManager = $this->getEntityManager();
        $applications = [];
        for ($i = 0; $i < $values['count']; $i++) {
            $application = new ApplicationEntity(true);
            $applications[] = $application;
            $event->addApplication($application);
            $application->setByValueArray($values);
            $application->setNextNumber($entityManager);
            $entityManager->persist($application);
            $this->choiceManager->createAdditionChoicesToApplication($values['addittions'], $application);
            /*foreach ($values['addittions'] as $additionIdAlphaNumeric => $optionIds) {
                //$additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
                if (!is_array($optionIds)) {
                    $optionIds = [$optionIds];
                }
                foreach ($optionIds as $optionId) {
                    $this->addChoice($optionId, $application);
                }
            }*/
        }
        $entityManager->flush();
        if ($values['delegateTo']) {
            $this->reservationManager->delegateNewReservations($applications, $values);
        }
    }
}