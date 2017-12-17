<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 19:19
 */

namespace App\Controls;

use Kdyby\Translation\ITranslator;
use Nette\SmartObject;

class ControlDependencies {
    use SmartObject, TInjectTranslator;

    public function __construct(ITranslator $translator) {
        $this->injectTranslator($translator);
    }

}