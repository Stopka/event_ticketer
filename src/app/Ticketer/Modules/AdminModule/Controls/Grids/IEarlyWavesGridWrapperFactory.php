<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

interface IEarlyWavesGridWrapperFactory
{
    public function create(): EarlyWavesGridWrapper;
}
