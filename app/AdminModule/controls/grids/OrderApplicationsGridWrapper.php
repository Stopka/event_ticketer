<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 2.2.17
 * Time: 0:00
 */

namespace App\AdminModule\Controls\Grids;


use App\Grids\Grid;

class OrderApplicationsGridWrapper extends \App\FrontModule\Controls\Grids\OrderApplicationsGridWrapper {
    protected function appendApplicationColumns(Grid $grid) {
        parent::appendApplicationColumns($grid);
        $grid->addColumnDate('birthDate','Datum narození');
        $grid->addColumnText('birthCode','Kod rodného čísla');
        $grid->addColumnText('gender','Pohlaví')
            ->setReplacement([0=>'Muž',1=>'Žena']);
    }

}