<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OrderEntity;
use Tracy\Debugger;

class OrderFacade extends EntityFacade {

    protected function getEntityClass() {
        return OrderEntity::class;
    }

    public function createOrderFromOrderForm($values, EventEntity $event = null, EarlyEntity $early = null) {
        $em = $this->getEntityManager();
        Debugger::barDump($values);
    }
}