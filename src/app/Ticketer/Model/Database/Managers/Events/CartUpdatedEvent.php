<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Ticketer\Model\Database\Entities\CartEntity;

class CartUpdatedEvent extends Event
{
    private CartEntity $cart;

    public function __construct(CartEntity $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return CartEntity
     */
    public function getCart(): CartEntity
    {
        return $this->cart;
    }
}
