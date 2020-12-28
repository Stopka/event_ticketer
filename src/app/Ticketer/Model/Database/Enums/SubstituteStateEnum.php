<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

/**
 * @extends Enum<int>
 * @method static self WAITING()
 * @method static self ACTIVE()
 * @method static self ORDERED()
 * @method static self OVERDUE()
 */
final class SubstituteStateEnum extends Enum
{
    public const WAITING = 0;
    public const ACTIVE = 1;
    public const ORDERED = 2;
    public const OVERDUE = 4;

    public function isActivable(): bool
    {
        return $this->inArray(self::listActivable());
    }

    /**
     * @return SubstituteStateEnum[]
     */
    public static function listActivable(): array
    {
        return [
            self::WAITING(),
            self::OVERDUE(),
        ];
    }
}
