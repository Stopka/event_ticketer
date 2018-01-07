<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Persistence\Attribute\ISortableEntity;
use App\Model\Persistence\Attribute\TPositionAttribute;

class PositionSorter {

    /**
     * @param TPositionAttribute[]|\Traversable $entities
     */
    public function recalculatePositions(iterable $entities): void{
        $position = 1;
        foreach ($entities as $entity){
            $entity->setPosition($position);
            $position++;
        }
    }

    /**
     * @param ISortableEntity $item
     * @param ISortableEntity[]|\Traversable $entities
     */
    public function moveEntityUp(ISortableEntity $item, iterable $entities): void{
        $position = 1;
        /** @var ISortableEntity $previousEntity */
        $previousEntity = null;
        foreach ($entities as $entity){
            if($item->getID() == $entity->getID() && $previousEntity){
                $previousEntity->setPosition($position);
                $entity->setPosition($position-1);
            }else{
                $entity->setPosition($position);
            }
            $previousEntity = $entity;
            $position++;
        }
    }

    /**
     * @param ISortableEntity $item
     * @param ISortableEntity[]|\Traversable $entities
     */
    public function moveEntityDown(ISortableEntity $item, iterable $entities): void{
        $position = 1;
        $shift=0;
        foreach ($entities as $entity){
            if($item->getID() == $entity->getID()){
                $entity->setPosition($position+1);
                $shift++;
            }else{
                $entity->setPosition($position - $shift);
                if($shift){
                    $shift--;
                }
            }
            $position++;
        }
    }

    /**
     * @param ISortableEntity $item
     * @param ISortableEntity[]|\Traversable $entities
     */
    public function moveEntityToEnd(ISortableEntity $item, iterable $entities): void{
        $position = 1;
        foreach ($entities as $entity){
            if($item->getID() == $entity->getID()){
                continue;
            }else{
                $entity->setPosition($position);
            }
            $position++;
        }
        $item->setPosition($position);
    }

    /**
     * @param ISortableEntity $item
     * @param ISortableEntity[]|\Traversable $entities
     */
    public function moveEntityToStart(ISortableEntity $item, iterable $entities): void{
        $item->setPosition(1);
        $position = 2;
        foreach ($entities as $entity){
            if($item->getID() == $entity->getID()){
                continue;
            }else{
                $entity->setPosition($position);
            }
            $position++;
        }
    }
}