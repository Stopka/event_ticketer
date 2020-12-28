<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

/**
 * @extends Enum<int>
 * @method static ReservationStateEnum WAITING()
 * @method static ReservationStateEnum ORDERED()
 */
final class ReservationStateEnum extends Enum
{
    public const WAITING = 0;
    public const ORDERED = 1;
}
