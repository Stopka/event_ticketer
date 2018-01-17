<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\EmptyException;
use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\ReservationDao;
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

    /** @var OptionDao */
    private $optionDao;

    /** @var ReservationDao */
    private $reservationDao;

    /**
     * ReservationManager constructor.
     * @param EntityManager $entityManager
     * @param OptionDao $optionDao
     * @param ReservationDao $reservationDao
     */
    public function __construct(EntityManager $entityManager, OptionDao $optionDao, ReservationDao $reservationDao) {
        $this->injectEntityManager($entityManager);
        $this->optionDao = $optionDao;
        $this->reservationDao = $reservationDao;
    }

    /** @var callable[]  */
    public $onReservationDelegated = array();

    /**
     * @param ApplicationEntity[] $applications
     * @param array $values
     * @throws \Exception
     * @throws EmptyException
     */
    public function delegateNewReservations(array $applications, array $values){
        $entityManager = $this->getEntityManager();
        if (!count($applications)) { //if no applications to delegate
            throw new EmptyException("Error.Reservation.Application.Empty");
        }
        if (!$values['delegateTo']) { //If delegated to nobody
            // nothing to do
            return;
        }
        if ($values['delegateTo'] == '*') { //If delegateed to new person
            //Create reservation
            $reservation = new ReservationEntity();
            $reservation->setByValueArray($values['delegateNew']);
            $entityManager->persist($reservation);
        } else { //else means delegated to existing person
            //find reservation
            $reservation = $this->reservationDao->getReservation($values['delegateTo']);
            if (!$reservation->isRegisterReady()) { //If reservation is already ordered
                //Create new one with same values
                $newReservation = new ReservationEntity();
                $reservationValues = $reservation->getValueArray(null, 'applications');
                $newReservation->setByValueArray($reservationValues);
                $entityManager->persist($newReservation);
                $reservation = $newReservation;
            }
        }
        foreach ($applications as $application){
            $oldReservation = $application->getReservation();
            $reservation->addApplication($application);
            // if application was delegated previously and now reservation is empty
            if ($oldReservation && !count($oldReservation->getApplications())) {
                // remove old reservation
                $entityManager->remove($oldReservation);
            }
        }
        $entityManager->flush();
        $this->onReservationDelegated($reservation);
    }

    /**
     * @param string $optionId
     * @param ApplicationEntity $application
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
                    $this->addChoice($optionId, $application);
                }
            }
        }
        $entityManager->flush();
        if ($values['delegateTo']) {
            $this->delegateNewReservations($applications, $values);
        }
    }
}