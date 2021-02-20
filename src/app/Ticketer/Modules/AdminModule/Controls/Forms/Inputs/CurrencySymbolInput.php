<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\TextInput;
use Ticketer\Controls\Forms\Form;

class CurrencySymbolInput extends TextInput
{
    public function __construct($label = 'Attribute.Currency.Symbol')
    {
        parent::__construct($label);
        $this->setOption(Form::OPTION_KEY_DESCRIPTION, "Form.Currency.Description.Symbol");
        $this->addRule(Form::MIN_LENGTH, null, 1);
    }
}
