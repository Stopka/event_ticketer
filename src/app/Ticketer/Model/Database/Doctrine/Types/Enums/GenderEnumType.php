<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Ticketer\Model\Database\Enums\GenderEnum;

/**
 * @extends IntegerEnumType<GenderEnum>
 */
class GenderEnumType extends IntegerEnumType
{
    protected function getEnumClassName(): string
    {
        return GenderEnum::class;
    }

    public function getName(): string
    {
        return 'GenderEnum';
    }
}
