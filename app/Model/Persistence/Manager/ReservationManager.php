<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\EmptyException;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
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
}