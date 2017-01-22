<?php

namespace App\CompanyModule\Controls\Forms;

interface ISubstituteFormWrapperFactory{

    /**
     * @return SubstituteFormWrapper
     */
    public function create();
}
