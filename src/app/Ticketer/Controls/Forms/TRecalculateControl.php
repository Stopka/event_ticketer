<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Nette\Localization\ITranslator;
use Nette\Utils\Html;

trait TRecalculateControl
{
    abstract protected function getTranslator(): ITranslator;

    protected function createRecalculateHtml(): Html
    {
        $label = 'Form.Action.Recalculate';
        /** @var null|ITranslator $translator */
        $translator = $this->getTranslator();
        if (null !== $translator) {
            $label = $translator->translate($label);
        }

        return Html::el('a', ['href' => '#', 'class' => 'price_recalculate', 'title' => 'Přepočítat'])
            ->addHtml(Html::el('i', ['class' => 'fa fa-calculator']))
            ->addText(' ')
            ->addHtml(Html::el('span')->addText($label));
    }
}
