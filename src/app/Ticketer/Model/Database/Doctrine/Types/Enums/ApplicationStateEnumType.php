<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Ticketer\Model\Database\Enums\ApplicationStateEnum;

/**
 * @extends IntegerEnumType<ApplicationStateEnum>
 */
class ApplicationStateEnumType extends IntegerEnumType
{
    protected function getEnumClassName(): string
    {
        return ApplicationStateEnum::class;
    }

    public function getName(): string
    {
        return 'ApplicationStateEnum';
    }
}
