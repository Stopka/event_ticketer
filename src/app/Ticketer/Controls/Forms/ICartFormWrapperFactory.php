<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

interface ICartFormWrapperFactory
{
    /**
     * @param bool $admin
     * @return CartFormWrapper
     */
    public function create(bool $admin = false): CartFormWrapper;
}
