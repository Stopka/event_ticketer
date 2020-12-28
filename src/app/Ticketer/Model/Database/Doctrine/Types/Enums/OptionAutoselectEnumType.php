<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine\Types\Enums;

use Ticketer\Model\Database\Enums\OptionAutoselectEnum;

/**
 * @extends IntegerEnumType<OptionAutoselectEnum>
 */
class OptionAutoselectEnumType extends IntegerEnumType
{

    protected function getEnumClassName(): string
    {
        return OptionAutoselectEnum::class;
    }

    public function getName(): string
    {
        return 'OptionAutoselectEnum';
    }
}
