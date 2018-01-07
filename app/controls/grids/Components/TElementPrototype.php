<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 7.1.18
 * Time: 16:47
 */

namespace App\Controls\Grids\Components;


use Nette\Utils\Html;

trait TElementPrototype {

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    abstract public function getOption($key, $default = NULL);

    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @return Html
     */
    public function getElementPrototype() {
        $element = parent::getElementPrototype();
        $innerHtml = Html::el();
        if ($iconOption = $this->getOption('icon')) {
            $innerHtml->addHtml(
                Html::el('i', [
                    'class' => $iconOption
                ])
            );
            $innerHtml->addText(' ');
        }
        $innerHtml->addHtml(
            Html::el('span', [
                'class' => 'grid-action-label'
            ])
                ->setText($this->getLabel())
        );
        $element->setHtml($innerHtml);
        return $element;
    }
}