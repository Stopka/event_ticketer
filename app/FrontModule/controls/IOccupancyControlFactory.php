<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 28.11.17
 * Time: 12:44
 */

namespace App\FrontModule\Controls;


interface IOccupancyControlFactory {

    /**
     * @return OccupancyControl
     */
    public function create(): OccupancyControl;
}