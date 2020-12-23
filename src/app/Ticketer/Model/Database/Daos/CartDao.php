<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\CartEntity;

class CartDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return CartEntity::class;
    }

    /**
     * @param string $uid
     * @return CartEntity|null
     */
    public function getViewableCartByUid(string $uid): ?CartEntity
    {
        /** @var CartEntity|null $cart */
        $cart = $this->getRepository()->findOneBy(['uid' => $uid]);
        if (null !== $cart && CartEntity::STATE_ORDERED === $cart->getState()) {
            return $cart;
        }

        return null;
    }

    /**
     * @param int|null $id
     * @return null|CartEntity
     */
    public function getCart(?int $id): ?CartEntity
    {
        /** @var CartEntity|null $result */
        $result = $this->get($id);

        return $result;
    }
}
