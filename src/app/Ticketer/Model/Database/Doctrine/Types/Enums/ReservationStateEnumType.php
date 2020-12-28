<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Ticketer\Model\Database\Enums\ReservationStateEnum;

/**
 * @extends IntegerEnumType<ReservationStateEnum>
 */
class ReservationStateEnumType extends IntegerEnumType
{

    protected function getEnumClassName(): string
    {
        return ReservationStateEnum::class;
    }

    public function getName(): string
    {
        return 'ReservationStateEnum';
    }
}
