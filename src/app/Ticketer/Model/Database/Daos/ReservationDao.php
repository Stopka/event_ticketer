<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\ReservationEntity;

class ReservationDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return ReservationEntity::class;
    }

    /**
     * @param EventEntity $event
     * @return ReservationEntity[]
     */
    public function getEventReservations(EventEntity $event): array
    {
        return $this->getRepository()->findBy([
            'event.id' => $event->getId()
        ]);
    }

    /**
     * @param null|int $id
     * @return ReservationEntity|null
     */
    public function getReservation(?int $id): ?ReservationEntity
    {
        /** @var ReservationEntity|null $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * Returns reservation ready to register
     * @param string $id
     * @return ReservationEntity|null
     */
    public function getRegisterReadyReservationByUid(string $id): ?ReservationEntity
    {
        /** @var ReservationEntity|null $reservation */
        $reservation = $this->getRepository()->findOneBy(['uid' => $id]);
        if (null !== $reservation && $reservation->isRegisterReady()) {
            return $reservation;
        }

        return null;
    }

    /**
     * @param EventEntity $event
     * @return array<int|string>
     */
    public function getEventReservationList(EventEntity $event): array
    {
        $reservations = $this->getEventReservations($event);
        $list = [];
        foreach ($reservations as $reservation) {
            $list[$reservation->getId()] = $reservation->getFullName() . ' (' . $reservation->getEmail() . ')';
        }

        return $list;
    }
}
