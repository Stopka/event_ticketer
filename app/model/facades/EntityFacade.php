<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;

abstract class EntityFacade extends DoctrineFacade {

    abstract protected function getEntityClass();

    protected function get($id){
        if(!isset($id)){
            return NULL;
        }
        return $this->getEntityManager()->find($this->getEntityClass(),$id);
    }

}