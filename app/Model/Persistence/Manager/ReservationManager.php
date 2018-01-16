<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\InvalidStateException;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use Doctrine\ORM\EntityManager;
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
     * @param ApplicationEntity[] $applications
     * @param array $values
     */
    public function delegateNewReservations(array $applications, array $values){
        $entityManager = $this->getEntityManager();
        $reservation = new ReservationEntity();
        $reservation->setByValueArray($values);
        $entityManager->persist($reservation);
        $cart = new CartEntity(true);
        $cart->setReservation($reservation);
        $cart->setNextNumber($entityManager);
        $entityManager->persist($cart);
        foreach ($applications as $application){
            $oldCart = $application->getCart();
            if($oldCart->getState() !== CartEntity::STATE_RESERVED ||
                $application->getState() !== ApplicationEntity::STATE_RESERVED){
                throw new InvalidStateException("Error.Reservation.Application.InvalidState");
            }
            $application->setCart($cart);
            $application->updateState();
            if(!count($oldCart->getApplications())){
                $entityManager->remove($oldCart);
            }
        }
        $entityManager->flush();
        $this->onReservationDelegated();
    }
}