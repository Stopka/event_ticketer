<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Templates;

use Ticketer\Model\Database\Entities\CartEntity;

class CartTemplate extends BaseTemplate
{
    public ?CartEntity $cart;
}
