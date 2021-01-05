<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms\Inputs;

use Kdyby\Replicator\Container as KdybyContainer;
use Ticketer\Controls\Forms\Container;

class MultiplierContainer extends KdybyContainer
{
    public function __construct(callable $factory, int $createDefault = 0, bool $forceDefault = false)
    {
        parent::__construct($factory, $createDefault, $forceDefault);
        $this->containerClass = Container::class;
    }
}
