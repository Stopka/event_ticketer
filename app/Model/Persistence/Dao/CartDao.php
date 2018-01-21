<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\CartEntity;

class CartDao extends EntityDao {

    protected function getEntityClass(): string {
        return CartEntity::class;
    }

    /**
     * @param string $uid
     * @return CartEntity|null
     */
    public function getViewableCartByUid(string $uid): ?CartEntity {
        $cart = $this->getRepository()->findOneBy(['uid' => $uid]);
        if ($cart && $cart->getState() == CartEntity::STATE_ORDERED)
            return $cart;
        return NULL;
    }

    /**
     * @param $id
     * @return null|CartEntity
     */
    public function getCart(?string $id): ?CartEntity {
        /** @var CartEntity $result */
        $result = $this->get($id);
        return $result;
    }
}