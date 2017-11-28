<?php

namespace App\Controls\Forms;

interface IOrderFormWrapperFactory{

    /**
     * @return OrderFormWrapper
     */
    public function create(): OrderFormWrapper;
}
