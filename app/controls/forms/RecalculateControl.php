<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 22:14
 */

namespace App\Controls\Forms;


use Nette\Utils\Html;

trait RecalculateControl {

    protected function createRecalculateHtml() {
        return Html::el('a', ['href' => '#', 'class' => 'price_recalculate', 'title' => 'Přepočítat'])
            ->addHtml(Html::el('i', ['class' => 'fa fa-calculator']))
            ->addHtml(Html::el('span')->addText('Přepočítat'));

    }

}