<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\ChildEntity;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OptionEntity;
use App\Model\Entities\OrderEntity;
use Tracy\Debugger;

class OrderFacade extends EntityFacade {

    protected function getEntityClass() {
        return OrderEntity::class;
    }

    /**
     * @param $values array
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @return OrderEntity
     */
    public function createOrderFromOrderForm($values, EventEntity $event = null, EarlyEntity $early = null) {
        Debugger::barDump($values);
        $entityManager = $this->getEntityManager();
        $order = new OrderEntity();
        $order->setByValueArray($values);
        $order->setEarly($early);
        $order->setEvent($event);
        $entityManager->persist($order);
        $commonValues = $values['commons'];
        $optionRepository = $entityManager->getRepository(OptionEntity::class);
        foreach ($values['children'] as $childValues) {
            $child = new ChildEntity();
            $child->setByValueArray($commonValues);
            $child->setByValueArray($childValues['child']);
            $child->setParent($order);
            $entityManager->persist($child);
            foreach ($childValues['addittions'] as $additionId => $optionId) {
                $option = $optionRepository->find($optionId);
                $child->addOption($option);
            }
        }
        $entityManager->flush();
        return $order;
    }
}