<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\CartEntity;

class CartDao extends EntityDao
{
    protected function getEntityClass(): string
    {
        return CartEntity::class;
    }

    /**
     * @param Uuid $uuid
     * @return CartEntity|null
     */
    public function getViewableCartByUid(Uuid $uuid): ?CartEntity
    {
        $cart = $this->getCart($uuid);
        if (null !== $cart && CartEntity::STATE_ORDERED === $cart->getState()) {
            return $cart;
        }

        return null;
    }

    /**
     * @param Uuid $id
     * @return null|CartEntity
     */
    public function getCart(Uuid $id): ?CartEntity
    {
        /** @var CartEntity|null $result */
        $result = $this->get($id);

        return $result;
    }
}
