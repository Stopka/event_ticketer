<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\ReservationEntity;

class ReservationDao extends EntityDao {

    protected function getEntityClass(): string {
        return ReservationEntity::class;
    }

    /**
     * @param EventEntity $event
     * @return ReservationEntity[]
     */
    public function getEventReservations(EventEntity $event): array {
        return $this->getRepository()->findBy([
            'event.id' => $event->getId()
        ]);
    }

    /**
     * @param EventEntity $event
     * @return ReservationEntity[]
     */
    public function getEventReservationList(EventEntity $event): array {
        $reservations = $this->getEventReservations($event);
        $list = [];
        foreach ($reservations as $reservation) {
            $list[$reservation->getId()] = $reservation->getFullName() . ' (' . $reservation->getEmail() . ')';
        }
        return $list;
    }
}