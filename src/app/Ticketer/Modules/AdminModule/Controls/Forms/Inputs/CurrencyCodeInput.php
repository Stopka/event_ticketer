<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\TextInput;
use Ticketer\Controls\Forms\Form;

final class CurrencyCodeInput extends TextInput
{
    use FormAppendableTrait;

    public function __construct($label)
    {
        parent::__construct($label, 3);
        $this->setOption(Form::OPTION_KEY_DESCRIPTION, "Form.Currency.Description.Code")
            ->addRule(Form::PATTERN, "Form.Currency.Rule.Code.Pattern", '[A-Z]{3}');
    }

    public static function getDefaultCaption(): string
    {
        return 'Attribute.Currency.Code';
    }
}
