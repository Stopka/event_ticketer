<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

interface IOptionsGridWrapperFactory
{
    public function create(): OptionsGridWrapper;
}
