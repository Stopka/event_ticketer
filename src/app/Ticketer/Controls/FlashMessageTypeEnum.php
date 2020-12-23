<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use MyCLabs\Enum\Enum;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 20:28
 * @method static FlashMessageTypeEnum ERROR()
 * @method static FlashMessageTypeEnum WARNING()
 * @method static FlashMessageTypeEnum INFO()
 * @method static FlashMessageTypeEnum SUCCESS()
 * @extends Enum<string>
 */
final class FlashMessageTypeEnum extends Enum
{
    private const ERROR = "error";
    private const WARNING = "warning";
    private const INFO = "info";
    private const SUCCESS = "success";
}
