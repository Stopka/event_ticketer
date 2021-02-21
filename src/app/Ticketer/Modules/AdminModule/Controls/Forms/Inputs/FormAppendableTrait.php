<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use LogicException;
use Nette\ComponentModel\IComponent;
use Ticketer\Controls\Forms\Form;

trait FormAppendableTrait
{
    abstract public function __construct(string $caption);

    abstract public static function getDefaultCaption(): string;

    public static function appendToForm(Form $form, string $name, ?string $caption = null): static
    {
        if (null === $caption) {
            $caption = static::getDefaultCaption();
        }
        $component = new static($caption);
        $form->addComponent($component, $name);

        return $component;
    }
}
