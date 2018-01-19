<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\EmptyException;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\ReservationDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
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

    /** @var ReservationDao */
    private $reservationDao;

    /**
     * ReservationManager constructor.
     * @param EntityManager $entityManager
     * @param OptionDao $optionDao
     * @param ReservationDao $reservationDao
     */
    public function __construct(EntityManager $entityManager, ReservationDao $reservationDao) {
        $this->injectEntityManager($entityManager);
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
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onReservationDelegated($reservation);
    }
}