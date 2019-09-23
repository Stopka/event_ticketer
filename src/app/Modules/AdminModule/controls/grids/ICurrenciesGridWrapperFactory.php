<?php

namespace App\AdminModule\Controls\Grids;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
interface ICurrenciesGridWrapperFactory {

    /**
     * @return CurrenciesGridWrapper
     */
    public function create(): CurrenciesGridWrapper;
}