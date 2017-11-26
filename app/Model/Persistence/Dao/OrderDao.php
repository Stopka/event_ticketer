<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\OrderEntity;

class OrderDao extends EntityDao {

    protected function getEntityClass(): string {
        return OrderEntity::class;
    }

    /**
     * @param $hash string
     * @return \App\Model\Persistence\Entity\OrderEntity|null
     */
    public function getViewableOrder(?string $id): ?OrderEntity {
        $order = $this->getOrder($id);
        if ($order && $order->getState() == OrderEntity::STATE_ORDER)
            return $order;
        return NULL;
    }

    /**
     * @param $id
     * @return null|OrderEntity
     */
    public function getOrder(?string $id): ?OrderEntity {
        /** @var OrderEntity $result */
        $result = $this->get($id);
        return $result;
    }
}