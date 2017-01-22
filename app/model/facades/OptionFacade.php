<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\EventEntity;
use App\Model\Entities\OptionEntity;

class OptionFacade extends EntityFacade {

    protected function getEntityClass() {
        return OptionEntity::class;
    }

    /**
     * @param EventEntity $event
     * @return OptionEntity[]
     */
    public function getOptionsWithLimitedCapacity(EventEntity $event){
        return $this->getRepository()->findBy([
            'addition.event.id'=>$event->getId(),
            'capacity !='=>null
        ]);
    }

}