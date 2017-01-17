<?php

namespace App\Controls\Forms;

use Nette;
use Nette\Application\UI\Form;


abstract class FormFactory {
    use Nette\SmartObject;

    /**
     * @return Form
     */
    public function create($callback) {
        $form = new Form;
        return $form;
    }

}
