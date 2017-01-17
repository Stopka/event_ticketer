<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\Entities\EventEntity;

class EventFacade extends EntityFacade {

    protected function getEntityClass() {
        return EventEntity::class;
    }


    public function getActiveEvents() {

    }
}