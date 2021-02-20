<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\TextInput;
use Ticketer\Controls\Forms\Form;

class CurrencyCodeInput extends TextInput
{
    public function __construct($label = 'Attribute.Currency.Code')
    {
        parent::__construct($label, 3);
        $this->setOption(Form::OPTION_KEY_DESCRIPTION, "Form.Currency.Description.Code")
            ->addRule(Form::PATTERN, "Form.Currency.Rule.Code.Pattern", '[A-Z]{3}');
    }
}
