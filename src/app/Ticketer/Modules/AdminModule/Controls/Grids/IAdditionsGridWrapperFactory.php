<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

interface IAdditionsGridWrapperFactory
{
    public function create(): AdditionsGridWrapper;
}
