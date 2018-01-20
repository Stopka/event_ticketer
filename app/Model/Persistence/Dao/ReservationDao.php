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
     * @param null|int $id
     * @return ReservationEntity|null
     */
    public function getReservation(?int $id): ?ReservationEntity {
        /** @var ReservationEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * Returns reservation ready to register
     * @param string $id
     * @return ReservationEntity|null
     */
    public function getRegisterReadyReservationByUid(string $id): ?ReservationEntity {
        $reservation = $this->getRepository()->findOneBy(['uid' => $id]);
        if ($reservation && $reservation->isRegisterReady()) {
            return $reservation;
        }
        return null;
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