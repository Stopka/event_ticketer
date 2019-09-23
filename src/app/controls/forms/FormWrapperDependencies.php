<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 19:19
 */

namespace App\Controls\Forms;

use App\Controls\TControlDependencies;
use App\Controls\TInjectTranslator;
use Kdyby\Translation\ITranslator;
use Nette\SmartObject;

class FormWrapperDependencies {
    use SmartObject, TControlDependencies, TInjectTranslator;

    public function __construct(ITranslator $translator) {
        $this->injectTranslator($translator);
    }


}