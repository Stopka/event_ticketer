<?php

namespace App\FrontModule\Controls\Grids;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
interface IOrderApplicationsGridWrapperFactory {

    /**
     * @return OrderApplicationsGridWrapper
     */
    public function create(): OrderApplicationsGridWrapper;
}