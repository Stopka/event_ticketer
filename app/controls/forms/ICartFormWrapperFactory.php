<?php

namespace App\Controls\Forms;

interface ICartFormWrapperFactory{

    /**
     * @return CartFormWrapper
     */
    public function create(): CartFormWrapper;
}
