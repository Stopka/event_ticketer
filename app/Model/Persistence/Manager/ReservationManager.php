<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\EmptyException;
use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\ChoiceEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ReservationManager {
    use SmartObject, TDoctrineEntityManager;

    /**
     * ReservationManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->injectEntityManager($entityManager);
    }

    /** @var callable[]  */
    public $onReservationDelegated = array();

    /**
     * @param array $applications
     * @param array $values
     * @throws \Exception
     * @throws EmptyException
     */
    public function delegateNewReservations(array $applications, array $values){
        $entityManager = $this->getEntityManager();
        $reservation = new ReservationEntity();
        $reservation->setByValueArray($values);
        $entityManager->persist($reservation);
        if(!count($applications)){
            throw new EmptyException("Error.Reservation.Application.Empty");
        }
        foreach ($applications as $application){
            $reservation->addApplication($application);
        }
        $entityManager->flush();
        $this->onReservationDelegated();
    }

    /**
     * @param string $optionId
     * @return ChoiceEntity
     */
    private function addChoice(string $optionId, ApplicationEntity $application): ChoiceEntity {
        $option = $this->optionDao->getOption($optionId);
        if (!$option) {
            throw new NotFoundException("Option was not found.");
        }
        $choice = new ChoiceEntity();
        $choice->setOption($option);
        $choice->setApplication($application);
        $this->getEntityManager()->persist($choice);
        return $choice;
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
            foreach ($values['addittions'] as $additionIdAlphaNumeric => $optionIds) {
                //$additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
                if (!is_array($optionIds)) {
                    $optionIds = [$optionIds];
                }
                foreach ($optionIds as $optionId) {
                    $choice = $this->addChoice($optionId, $application);
                }
            }
        }
        $entityManager->flush();
        if ($values['delegated']) {
            $this->delegateNewReservations($applications, $values['reservation']);
        }
    }
}