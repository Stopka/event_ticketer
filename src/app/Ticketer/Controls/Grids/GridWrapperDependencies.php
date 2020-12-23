<?php

declare(strict_types=1);

namespace Ticketer\Controls\Grids;

use Nette\Localization\ITranslator;
use Ticketer\Controls\TControlDependencies;
use Ticketer\Controls\TInjectTranslator;
use Ticketer\Model\DateFormatter;
use Nette\SmartObject;

class GridWrapperDependencies
{
    use SmartObject;
    use TInjectTranslator;
    use TControlDependencies;

    /** @var DateFormatter */
    private $dateFormatter;

    public function __construct(ITranslator $translator, DateFormatter $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
        $this->injectTranslator($translator);
    }

    /**
     * @return DateFormatter
     */
    public function getDateFormatter(): DateFormatter
    {
        return $this->dateFormatter;
    }
}
