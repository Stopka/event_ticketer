<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

/**
 * Class GenderEnum
 * @method static GenderEnum MALE()
 * @method static GenderEnum FEMALE()
 * @extends  Enum<int>
 */
final class GenderEnum extends Enum
{
    public const MALE = 1;
    public const FEMALE = 0;
}
