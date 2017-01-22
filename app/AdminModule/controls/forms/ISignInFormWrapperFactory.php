<?php

namespace App\AdminModule\Controls\Forms;

interface ISignInFormWrapperFactory{

    /**
     * @return SignInFormWrapper
     */
    public function create();
}
