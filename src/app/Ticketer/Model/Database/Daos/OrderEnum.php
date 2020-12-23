<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use MyCLabs\Enum\Enum;

/**
 * Class OrderEnum
 * @package Ticketer\Model\Database\Daos
 * @extends  Enum<string>
 * @method static OrderEnum ASC()
 * @method static OrderEnum DESC()
 */
final class OrderEnum extends Enum
{
    private const ASC = 'ASC';
    private const DESC = 'DESC';
}
