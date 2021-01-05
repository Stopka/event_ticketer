<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Contributte\FormMultiplier\Multiplier;
use Nette\Forms\Container as NetteContainer;
use Stopka\NetteFormsCheckboxComponent\CheckboxControlContainerTrait;
use Stopka\NetteFormsHtmlComponent\HtmlControlContainerTrait;
use Ticketer\Controls\Forms\Inputs\MultiplierContainer;
use Vodacek\Forms\Controls\DateInput;

trait TContainerExtension
{
    use HtmlControlContainerTrait;
    use CheckboxControlContainerTrait;

    /**
     * @param int|string $name
     * @return Container
     */
    public function addContainer($name): NetteContainer
    {
        $control = new Container();
        $control->currentGroup = $this->currentGroup;
        if (null !== $this->currentGroup) {
            $this->currentGroup->add($control);
        }

        return $this[$name] = $control;
    }


    /**
     * @param string $name
     * @param string|null $label
     * @return DateInput
     */
    public function addDate(string $name, ?string $label): DateInput
    {
        $input = new DateInput($label, DateInput::TYPE_DATE, true);
        $this->addComponent($input, $name);

        return $input;
    }

    public function addMultiplier(
        string $name,
        callable $factory,
        int $createDefault = 0,
        bool $forceDefault = false
    ): MultiplierContainer {
        $multiplier = new MultiplierContainer($factory, $createDefault, $forceDefault);
        $multiplier->setCurrentGroup($this->getCurrentGroup());

        $this->addComponent($multiplier, $name);

        return $multiplier;
    }
}
