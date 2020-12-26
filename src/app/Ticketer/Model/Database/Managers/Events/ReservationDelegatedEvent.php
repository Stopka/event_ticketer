<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Ticketer\Model\Database\Entities\ReservationEntity;

class ReservationDelegatedEvent extends Event
{
    private ReservationEntity $reservation;

    public function __construct(ReservationEntity $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * @return ReservationEntity
     */
    public function getReservation(): ReservationEntity
    {
        return $this->reservation;
    }
}
