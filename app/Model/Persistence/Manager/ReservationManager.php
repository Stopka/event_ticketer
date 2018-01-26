<?php

namespace App\Model\Persistence\Manager;

use App\AdminModule\Controls\Forms\DelegateReservationControlsBuilder;
use App\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
use App\Model\Exception\EmptyException;
use App\Model\Persistence\Dao\ReservationDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ReservationManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var ReservationDao */
    private $reservationDao;

    /** @var ApplicationManager */
    private $applicationManager;

    /**
     * ReservationManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param ReservationDao $reservationDao
     * @param ApplicationManager $applicationManager
     */
    public function __construct(EntityManagerWrapper $entityManager, ReservationDao $reservationDao, ApplicationManager $applicationManager) {
        $this->injectEntityManager($entityManager);
        $this->reservationDao = $reservationDao;
        $this->applicationManager = $applicationManager;
    }

    /** @var callable[] */
    public $onReservationDelegated = array();

    /**
     * @param ApplicationEntity[] $applications
     * @param array $values
     * @throws \Exception
     * @throws EmptyException
     */
    public function delegateNewReservations(array $applications, array $values) {
        $entityManager = $this->getEntityManager();
        if (!count($applications)) { //if no applications to delegate
            throw new EmptyException("Error.Reservation.Application.Empty");
        }
        if (!$values[DelegateReservationControlsBuilder::FIELD_DELEGATE]) { //If delegated to nobody
            // nothing to do
            return;
        }
        if ($values[DelegateReservationControlsBuilder::FIELD_DELEGATE] == DelegateReservationControlsBuilder::VALUE_DELEGATE_NEW) { //If delegateed to new person
            //Create reservation
            $reservation = new ReservationEntity();
            $reservation->setByValueArray($values[DelegateReservationControlsBuilder::CONTAINER_NAME_NEW]);
            $entityManager->persist($reservation);
        } else { //else means delegated to existing person
            //find reservation
            $reservation = $this->reservationDao->getReservation($values[DelegateReservationControlsBuilder::FIELD_DELEGATE]);
            if (!$reservation->isRegisterReady()) { //If reservation is already ordered
                //Create new one with same values
                $newReservation = new ReservationEntity();
                $reservationValues = $reservation->getValueArray(null, 'applications');
                $newReservation->setByValueArray($reservationValues);
                $entityManager->persist($newReservation);
                $reservation = $newReservation;
            }
        }
        foreach ($applications as $application) {
            $oldReservation = $application->getReservation();
            $reservation->addApplication($application);
            // if application was delegated previously and now reservation is empty
            if ($oldReservation && !count($oldReservation->getApplications())) {
                // remove old reservation
                $entityManager->remove($oldReservation);
            }
        }
        $entityManager->flush();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onReservationDelegated($reservation);
    }

    /**
     * @param array $values
     * @param EventEntity|null $event
     * @throws \Exception
     */
    public function createReservedApplicationsFromReservationForm(array $values, EventEntity $event): void {
        $entityManager = $this->getEntityManager();
        $applications = [];
        for ($i = 0; $i < $values[ReserveApplicationFormWrapper::FIELD_COUNT]; $i++) {
            $applications[] = $this->applicationManager->createReservedApplicationFromReservationForm($values, $event);
        }
        $entityManager->flush();
        if ($values[DelegateReservationControlsBuilder::FIELD_DELEGATE]) {
            $this->delegateNewReservations($applications, $values);
        }
    }

    /**
     * @param array $values
     * @param ApplicationEntity[] $applicationEntities
     */
    public function editReservedApplicationsFromReservationForm(array $values, array $applicationEntities) {
        foreach ($applicationEntities as $applicationEntity) {
            $this->applicationManager->editReservedApplicationFromReservationForm($values, $applicationEntity);
        }
        $this->getEntityManager()->flush();
    }
}