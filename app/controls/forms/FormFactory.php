<?php

namespace App\Controls\Forms;

use Nette;
use Nette\Application\UI\Form;
use Stopka\NetteFormRenderer\FormRenderer;


abstract class FormFactory {
    use Nette\SmartObject;

    /**
     * @return Form
     */
    public function create() {
        $form = new Form;
        $form->setRenderer(new FormRenderer());
        return $form;
    }

}
