<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;

class OptionFacade extends EntityFacade {

    protected function getEntityClass() {
        return OptionEntity::class;
    }

    /**
     * @param \App\Model\Persistence\Entity\EventEntity $event
     * @return OptionEntity[]
     */
    public function getOptionsWithLimitedCapacity(EventEntity $event){
        return $this->getRepository()->findBy([
            'addition.event.id'=>$event->getId(),
            'capacity !='=>null
        ]);
    }

}