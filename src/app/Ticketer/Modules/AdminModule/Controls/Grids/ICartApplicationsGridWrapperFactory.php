<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

interface ICartApplicationsGridWrapperFactory
{
    public function create(): CartApplicationsGridWrapper;
}
