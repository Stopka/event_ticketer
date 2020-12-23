<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Nette\Localization\ITranslator;
use Ticketer\Controls\TControlDependencies;
use Ticketer\Controls\TInjectTranslator;
use Nette\SmartObject;

class FormWrapperDependencies
{
    use SmartObject;
    use TControlDependencies;
    use TInjectTranslator;

    public function __construct(ITranslator $translator)
    {
        $this->injectTranslator($translator);
    }
}
