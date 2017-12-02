<?php

namespace App\AdminModule\Controls\Forms;

interface ICurrencyFromWrapperFactory {

    /**
     * @return CurrencyFormWrapper
     */
    public function create(): CurrencyFormWrapper;
}
