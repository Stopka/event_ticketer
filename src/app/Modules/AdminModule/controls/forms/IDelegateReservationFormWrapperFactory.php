<?php

namespace App\AdminModule\Controls\Forms;

interface IDelegateReservationFormWrapperFactory{

    /**
     * @return DelegateReservationFormWrapper
     */
    public function create(): DelegateReservationFormWrapper;
}
