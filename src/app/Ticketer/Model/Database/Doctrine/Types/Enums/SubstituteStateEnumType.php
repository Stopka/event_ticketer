<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Ticketer\Model\Database\Enums\SubstituteStateEnum;

/**
 * @extends IntegerEnumType<SubstituteStateEnum>
 */
class SubstituteStateEnumType extends IntegerEnumType
{
    protected function getEnumClassName(): string
    {
        return SubstituteStateEnum::class;
    }

    public function getName(): string
    {
        return 'SubstituteStateEnum';
    }
}
