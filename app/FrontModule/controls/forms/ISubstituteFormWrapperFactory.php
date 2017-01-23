<?php

namespace App\FrontModule\Controls\Forms;

interface ISubstituteFormWrapperFactory{

    /**
     * @return SubstituteFormWrapper
     */
    public function create();
}
