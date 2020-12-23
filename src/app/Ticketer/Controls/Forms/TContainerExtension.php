<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Nette\Forms\Container as NetteContainer;
use Stopka\NetteFormsCheckboxComponent\CheckboxControlContainerTrait;
use Stopka\NetteFormsHtmlComponent\HtmlControlContainerTrait;

trait TContainerExtension
{
    use HtmlControlContainerTrait;
    use CheckboxControlContainerTrait;

    /**
     * @param int|string $name
     * @return NetteContainer
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
}
