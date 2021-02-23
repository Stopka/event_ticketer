<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\SubmitButton as NetteSubmitButton;
use Ticketer\Controls\Forms\Form;

final class PrimarySubmitButton extends NetteSubmitButton
{
    public function __construct(string $caption, callable $onClick)
    {
        parent::__construct($caption);
        $this->onClick[] = $onClick;
    }

    public static function appendToForm(Form $form, string $caption, callable $onClick, string $name = 'submit'): static
    {
        $component = new static($caption, $onClick);
        $form->addComponent($component, $name);

        return $component;
    }
}
