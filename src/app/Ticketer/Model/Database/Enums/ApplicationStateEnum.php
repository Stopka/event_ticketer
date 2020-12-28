<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

/**
 * @extends Enum<int>
 * @method static ApplicationStateEnum RESERVED()
 * @method static ApplicationStateEnum DELEGATED()
 * @method static ApplicationStateEnum WAITING()
 * @method static ApplicationStateEnum OCCUPIED()
 * @method static ApplicationStateEnum FULFILLED()
 * @method static ApplicationStateEnum CANCELLED()
 */
final class ApplicationStateEnum extends Enum
{
    public const RESERVED = 1;
    public const DELEGATED = 2;
    public const WAITING = 3;
    public const OCCUPIED = 4;
    public const FULFILLED = 5;
    public const CANCELLED = 6;

    public function isIssued(): bool
    {
        return !$this->inArray(self::listNotIssued());
    }

    public function isReserved(): bool
    {
        return $this->inArray(self::listReserved());
    }

    /**
     * @return self[]
     */
    public static function listNotIssued(): array
    {
        return [
            self::CANCELLED(),
        ];
    }

    /**
     * @return self[]
     */
    public static function listReserved(): array
    {
        return [self::RESERVED(), self::DELEGATED()];
    }

    /**
     * @return self[]
     */
    public static function listOccupied(): array
    {
        return [
            self::OCCUPIED(),
            self::FULFILLED(),
            //self::RESERVED(),
            //self::DELEGATED(),
        ];
    }

    /**
     * @return array<int,string>
     */
    public static function getLabels(): array
    {
        return [
            self::RESERVED => "Value.Application.State.Reserved",
            self::DELEGATED => "Value.Application.State.Delegated",
            self::WAITING => "Value.Application.State.Waiting",
            self::OCCUPIED => "Value.Application.State.Occupied",
            self::FULFILLED => "Value.Application.State.Fulfilled",
            self::CANCELLED => "Value.Application.State.Cancelled",
        ];
    }
}
