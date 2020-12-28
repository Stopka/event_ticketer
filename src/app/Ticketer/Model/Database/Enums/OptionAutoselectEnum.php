<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

/**
 * @extends Enum<int>
 * @method static OptionAutoselectEnum NONE()
 * @method static OptionAutoselectEnum ALWAYS()
 * @method static OptionAutoselectEnum SECOND_ON()
 */
final class OptionAutoselectEnum extends Enum
{
    public const NONE = 0;
    public const ALWAYS = 1;
    public const SECOND_ON = 2;
}
