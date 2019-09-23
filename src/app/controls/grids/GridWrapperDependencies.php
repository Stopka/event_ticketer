<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:38
 */

namespace App\Controls\Grids;


use App\Controls\TControlDependencies;
use App\Controls\TInjectTranslator;
use App\Model\DateFormatter;
use Kdyby\Translation\ITranslator;
use Nette\SmartObject;

class GridWrapperDependencies {
    use SmartObject, TInjectTranslator, TControlDependencies;

    /** @var DateFormatter */
    private $dateFormatter;

    public function __construct(ITranslator $translator, DateFormatter $dateFormatter) {
        $this->dateFormatter = $dateFormatter;
        $this->injectTranslator($translator);
    }

    /**
     * @return DateFormatter
     */
    public function getDateFormatter(): DateFormatter {
        return $this->dateFormatter;
    }

}