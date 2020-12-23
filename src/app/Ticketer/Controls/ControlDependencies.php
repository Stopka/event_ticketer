<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use Nette\Localization\ITranslator;
use Nette\SmartObject;

class ControlDependencies
{
    use SmartObject;
    use TInjectTranslator;

    public function __construct(ITranslator $translator)
    {
        $this->injectTranslator($translator);
    }
}
