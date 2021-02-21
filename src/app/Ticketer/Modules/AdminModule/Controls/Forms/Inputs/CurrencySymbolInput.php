<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\TextInput;
use Ticketer\Controls\Forms\Form;

final class CurrencySymbolInput extends TextInput
{
    use FormAppendableTrait;

    public function __construct(string $label)
    {
        parent::__construct($label);
        $this->setOption(Form::OPTION_KEY_DESCRIPTION, "Form.Currency.Description.Symbol");
        $this->addRule(Form::MIN_LENGTH, null, 1);
    }

    public static function getDefaultCaption(): string
    {
        return 'Attribute.Currency.Symbol';
    }
}
