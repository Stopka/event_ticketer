<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use Nette\Localization\ITranslator;

trait TControlDependencies
{

    abstract protected function getTranslator(): ITranslator;

    public function getControlDependencies(): ControlDependencies
    {
        return new ControlDependencies($this->getTranslator());
    }
}
