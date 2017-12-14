<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:38
 */

namespace App\Controls\Grids;


use App\Model\DateFormatter;
use Nette\Localization\ITranslator;
use Nette\SmartObject;

class GridWrapperDependencies {
    use SmartObject;

    /** @var  ITranslator */
    private  $translator;

    /** @var DateFormatter */
    private $dateFormatter;

    public function __construct(ITranslator $translator, DateFormatter $dateFormatter) {
        $this->translator = $translator;
        $this->dateFormatter = $dateFormatter;
    }

    /**
     * @return ITranslator
     */
    public function getTranslator(): ITranslator {
        return $this->translator;
    }

    /**
     * @return DateFormatter
     */
    public function getDateFormatter(): DateFormatter {
        return $this->dateFormatter;
    }

}