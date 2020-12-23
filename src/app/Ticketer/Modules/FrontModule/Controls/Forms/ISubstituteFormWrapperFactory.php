<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls\Forms;

interface ISubstituteFormWrapperFactory
{

    /**
     * @return SubstituteFormWrapper
     */
    public function create(): SubstituteFormWrapper;
}
