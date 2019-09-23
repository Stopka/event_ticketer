<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 19:19
 */

namespace App\Controls;

use Kdyby\Translation\ITranslator;

trait TControlDependencies {

    abstract function getTranslator(): ?ITranslator;

    public function getControlDependencies():ControlDependencies{
        return new ControlDependencies($this->getTranslator());
    }

}