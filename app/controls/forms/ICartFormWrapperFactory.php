<?php

namespace App\Controls\Forms;

interface ICartFormWrapperFactory{

    /**
     * @param bool $admin
     * @return CartFormWrapper
     */
    public function create(bool $admin = false): CartFormWrapper;
}
