<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 19:19
 */

namespace App\Controls\Forms;


use Nette\Localization\ITranslator;
use Nette\SmartObject;

class FormWrapperDependencies {
    use SmartObject;

    /** @var ITranslator */
    private $translator;

    public function __construct(ITranslator $translator) {
        $this->setTranslator($translator);
    }

    /**
     * @return ITranslator
     */
    public function getTranslator(): ITranslator {
        return $this->translator;
    }

    /**
     * @param ITranslator $translator
     */
    public function setTranslator(ITranslator $translator): void {
        $this->translator = $translator;
    }


}