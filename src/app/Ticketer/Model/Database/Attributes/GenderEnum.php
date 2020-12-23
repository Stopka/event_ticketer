<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use MyCLabs\Enum\Enum;

/**
 * Class GenderEnum
 * @package Ticketer\Model\Database\Attributes
 * @method static GenderEnum MALE()
 * @method static GenderEnum FEMALE()
 * @extends  Enum<int>
 */
class GenderEnum extends Enum
{
    private const MALE = 0;
    private const FEMALE = 1;
}
