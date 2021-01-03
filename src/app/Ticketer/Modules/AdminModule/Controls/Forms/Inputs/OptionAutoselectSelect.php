<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;

use InvalidArgumentException;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Ticketer\Model\Database\Enums\OptionAutoselectEnum;

class OptionAutoselectSelect extends SelectBox
{
    /**
     * OptionAutoselectSelect constructor.
     * @param string|object|null $label
     */
    public function __construct($label = null)
    {
        parent::__construct(
            $label,
            [
                OptionAutoselectEnum::NONE => 'Value.Addition.AutoSelect.None',
                OptionAutoselectEnum::ALWAYS => "Value.Addition.AutoSelect.Always",
                OptionAutoselectEnum::SECOND_ON => "Value.Addition.AutoSelect.SecondOn",
            ]
        );
    }

    /**
     * @param int|string|null|OptionAutoselectEnum|mixed $value
     * @return $this
     */
    public function setValue($value = null): self
    {
        if (null === $value || $value instanceof OptionAutoselectEnum) {
            $this->value = $value;
        } elseif (is_string($value)) {
            if ('' === $value) {
                $this->value = null;
            } else {
                $this->value = new OptionAutoselectEnum((int)$value);
            }
        } else {
            throw new InvalidArgumentException("Invalid type for $value.");
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
