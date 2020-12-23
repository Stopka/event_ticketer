<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

interface IEarlyWaveFormWrapperFactory
{

    public function create(): EarlyWaveFormWrapper;
}
