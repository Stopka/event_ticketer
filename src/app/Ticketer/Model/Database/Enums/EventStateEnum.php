<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

/**
 * @extends Enum<int>
 * @method static EventStateEnum INACTIVE()
 * @method static EventStateEnum ACTIVE()
 * @method static EventStateEnum CLOSED()
 * @method static EventStateEnum CANCELLED()
 */
final class EventStateEnum extends Enum
{
    public const INACTIVE = 0;
    public const ACTIVE = 1;
    public const CLOSED = 2;
    public const CANCELLED = 3;
}
