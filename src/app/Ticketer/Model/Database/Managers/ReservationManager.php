<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Ticketer\Model\Database\Managers\Events\ReservationDelegatedEvent;
use Ticketer\Modules\AdminModule\Controls\Forms\DelegateReservationControlsBuilder;
use Ticketer\Modules\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
use Ticketer\Model\Exceptions\EmptyException;
use Ticketer\Model\Database\Daos\ReservationDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;

class ReservationManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var ReservationDao */
    private $reservationDao;

    /** @var ApplicationManager */
    private $applicationManager;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * ReservationManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param ReservationDao $reservationDao
     * @param ApplicationManager $applicationManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerWrapper $entityManager,
        ReservationDao $reservationDao,
        ApplicationManager $applicationManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->injectEntityManager($entityManager);
        $this->reservationDao = $reservationDao;
        $this->applicationManager = $applicationManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ApplicationEntity[] $applications
     * @param array<mixed> $values
     * @throws \Exception
     * @throws EmptyException
     */
    public function delegateNewReservations(array $applications, array $values): void
    {
        $entityManager = $this->getEntityManager();
        if (0 === count($applications)) { //if no applications to delegate
            throw new EmptyException("Error.Reservation.Application.Empty");
        }
        if (!(bool)$values[DelegateReservationControlsBuilder::FIELD_DELEGATE]) {
            //If delegated to nobody
            // nothing to do
            return;
        }
        if (
            DelegateReservationControlsBuilder::VALUE_DELEGATE_NEW
            === $values[DelegateReservationControlsBuilder::FIELD_DELEGATE]
        ) { //If delegateed to new person
            //Create reservation
            $reservation = new ReservationEntity();
            $reservation->setByValueArray($values[DelegateReservationControlsBuilder::CONTAINER_NAME_NEW]);
            $entityManager->persist($reservation);
        } else { //else means delegated to existing person
            //find reservation
            $reservation = $this->reservationDao->getReservation(
                $values[DelegateReservationControlsBuilder::FIELD_DELEGATE]
            );
            if (null === $reservation) {
                return;
            }
            if (!$reservation->isRegisterReady()) { //If reservation is already ordered
                //Create new one with same values
                $newReservation = new ReservationEntity();
                $reservationValues = $reservation->getValueArray(null, ['applications']);
                $newReservation->setByValueArray($reservationValues);
                $entityManager->persist($newReservation);
                $reservation = $newReservation;
            }
        }
        foreach ($applications as $application) {
            $oldReservation = $application->getReservation();
            $reservation->addApplication($application);
            // if application was delegated previously and now reservation is empty
            if (null !== $oldReservation && 0 === count($oldReservation->getApplications())) {
                // remove old reservation
                $entityManager->remove($oldReservation);
            }
        }
        $entityManager->flush();
        $this->eventDispatcher->dispatch(new ReservationDelegatedEvent($reservation));
    }

    /**
     * @param array<mixed> $values
     * @param EventEntity $event
     * @throws \Exception
     */
    public function createReservedApplicationsFromReservationForm(array $values, EventEntity $event): void
    {
        $entityManager = $this->getEntityManager();
        $applications = [];
        for ($i = 0; $i < $values[ReserveApplicationFormWrapper::FIELD_COUNT]; $i++) {
            $applications[] = $this->applicationManager->createReservedApplicationFromReservationForm($values, $event);
        }
        $entityManager->flush();
        if ((bool)$values[DelegateReservationControlsBuilder::FIELD_DELEGATE]) {
            $this->delegateNewReservations($applications, $values);
        }
    }

    /**
     * @param array<mixed> $values
     * @param ApplicationEntity[] $applicationEntities
     */
    public function editReservedApplicationsFromReservationForm(array $values, array $applicationEntities): void
    {
        foreach ($applicationEntities as $applicationEntity) {
            $this->applicationManager->editReservedApplicationFromReservationForm($values, $applicationEntity);
        }
        $this->getEntityManager()->flush();
    }
}
