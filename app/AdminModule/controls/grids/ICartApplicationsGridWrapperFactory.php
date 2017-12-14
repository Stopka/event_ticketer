<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 2.2.17
 * Time: 0:01
 */

namespace App\AdminModule\Controls\Grids;


interface ICartApplicationsGridWrapperFactory {

    /**
     * @return CartApplicationsGridWrapper
     */
    public function create(): CartApplicationsGridWrapper;
}