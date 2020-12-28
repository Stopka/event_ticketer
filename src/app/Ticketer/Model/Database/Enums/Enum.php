<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Enums;

use MyCLabs\Enum\Enum as MyCLabsEnum;

/**
 * @template T
 * @extends  MyCLabsEnum<T>
 */
abstract class Enum extends MyCLabsEnum
{
    /**
     * @param static[] $states
     * @return bool
     */
    public function inArray(array $states): bool
    {
        foreach ($states as $state) {
            if ($state->equals($this)) {
                return true;
            }
        }

        return false;
    }
}
