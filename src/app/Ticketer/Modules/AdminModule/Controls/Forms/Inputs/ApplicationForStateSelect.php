<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;

class ApplicationForStateSelect extends SelectBox
{
    /**
     * ApplicationForStateSelect constructor.
     * @param string|object|null $label
     */
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

    /**
     * @param string|int|null|ApplicationStateEnum|mixed $value
     * @return $this
     */
    public function setValue($value = null): self
    {
        if (null === $value || $value instanceof ApplicationStateEnum) {
            $this->value = $value;
        } elseif (is_string($value)) {
            if ('' === $value) {
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
        return null !== $this->value && array_key_exists($this->value->getValue(), $this->items)
            ? $this->value
            : null;
    }
}
