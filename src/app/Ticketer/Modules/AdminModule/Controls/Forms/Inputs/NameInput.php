<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\TextInput;
use Ticketer\Controls\Forms\Form;

class NameInput extends TextInput
{
    public function __construct($label = 'Attribute.Name')
    {
        parent::__construct($label);
        $this->addRule(Form::MIN_LENGTH, null, 2);
    }
}
