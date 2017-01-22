<?php

namespace App\AdminModule\Controls\Grids;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class ApplicationsGridWrapper extends GridWrapper {

    function configure(\App\Grids\Grid $grid) {
        $grid->addColumnText('fullName','Jméno');
    }
}