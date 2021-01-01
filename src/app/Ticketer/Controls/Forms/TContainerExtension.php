<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Nette\Forms\Container as NetteContainer;
use Stopka\NetteFormsCheckboxComponent\CheckboxControlContainerTrait;
use Stopka\NetteFormsHtmlComponent\HtmlControlContainerTrait;
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
}
