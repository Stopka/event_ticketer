<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Ticketer\Model\Database\Enums\EventStateEnum;

/**
 * @extends IntegerEnumType<EventStateEnum>
 */
class EventStateEnumType extends IntegerEnumType
{

    protected function getEnumClassName(): string
    {
        return EventStateEnum::class;
    }

    public function getName(): string
    {
        return 'EventStateEnum';
    }
}
