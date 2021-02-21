<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\TextInput;
use Ticketer\Controls\Forms\Form;

final class NameInput extends TextInput
{
    use FormAppendableTrait;

    public function __construct(string $label)
    {
        parent::__construct($label);
        $this->addRule(Form::MIN_LENGTH, null, 2);
    }

    public static function getDefaultCaption(): string
    {
        return 'Attribute.Name';
    }
}
