<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls;

interface IOccupancyControlFactory
{

    /**
     * @return OccupancyControl
     */
    public function create(): OccupancyControl;
}
