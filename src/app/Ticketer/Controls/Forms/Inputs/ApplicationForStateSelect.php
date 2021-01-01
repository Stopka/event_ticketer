<?php

declare(strict_types=1);


namespace Ticketer\Controls\Forms\Inputs;


use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;

class ApplicationForStateSelect extends SelectBox
{
    public function __construct($label = null)
    {
        parent::__construct(
            $label,
            [
                ApplicationStateEnum::OCCUPIED => 'Value.ForState.Occupied',
                ApplicationStateEnum::FULFILLED => 'Value.ForState.Fulfilled',
            ]
        );
        $this->setPrompt('Value.ForState.None');
    }

    public function setValue($value = null): self
    {
        if ($value === null || $value instanceof ApplicationStateEnum) {
            $this->value = $value;
        } elseif (is_string($value)) {
            if ($value === '') {
                $this->value = null;
            } else {
                $this->value = new ApplicationStateEnum((int)$value);
            }
        } else {
            throw new \InvalidArgumentException("Invalid type for $value.");
        }

        return $this;
    }

    public function loadHttpData(): void
    {
        $this->setValue($this->getHttpData(Form::DATA_TEXT));
    }

    public function getValue()
    {
        return $this->value !== null && array_key_exists($this->value->getValue(), $this->items)
            ? $this->value
            : null;
    }


}
