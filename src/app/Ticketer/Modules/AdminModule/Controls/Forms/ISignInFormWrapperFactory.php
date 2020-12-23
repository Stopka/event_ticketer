<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

interface ISignInFormWrapperFactory
{

    public function create(): SignInFormWrapper;
}
