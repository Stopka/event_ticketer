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

    /**
     * Started end active events
     * @return EventEntity[]
     */
    public function getStartedEvents() {
        return $this->getRepository()->findBy([
            'state' => EventEntity::STATE_ACTIVE,
            'startDate <=' => new \DateTime()
        ],['startDate'=>self::ORDER_ASC]);
    }

    /**
     * Finds one active and started event if exists
     * @param $id
     * @return null|EventEntity
     */
    public function getStartedEvent($id){
        if(!$id){
            return NULL;
        }
        $event = $this->get($id);
        if($event&&$event->isActive()&&$event->isStarted()){
            return $event;
        }
        return NULL;
    }
}