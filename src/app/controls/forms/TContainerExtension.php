<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 9.12.17
 * Time: 14:05
 */

namespace App\Controls\Forms;


use Stopka\NetteFormRenderer\Forms\TContainerHtmlControl;
use Stopka\NetteFormRenderer\Forms\TContainerStandardizedCheckboxControl;

trait TContainerExtension {
    use TContainerHtmlControl, TContainerStandardizedCheckboxControl;

    public function addCheckbox($name, $caption = null) {
        return $this->addStandardizedCheckbox($name, $caption);
    }

    public function addContainer($name) {
        $control = new Container();
        $control->currentGroup = $this->currentGroup;
        if ($this->currentGroup !== null) {
            $this->currentGroup->add($control);
        }
        return $this[$name] = $control;
    }
}