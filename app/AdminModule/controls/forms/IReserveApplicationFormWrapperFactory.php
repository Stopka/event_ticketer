<?php

namespace App\AdminModule\Controls\Forms;

interface IReserveApplicationFormWrapperFactory{

    /**
     * @return ReserveApplicationFormWrapper
     */
    public function create(): ReserveApplicationFormWrapper;
}
