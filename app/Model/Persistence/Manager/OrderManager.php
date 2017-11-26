<?php

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\ChoiceEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OrderEntity;
use App\Model\Persistence\Factory\SubstituteEntity;
use Nette\Object;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class OrderManager extends Object {
    use TDoctrineEntityManager;

    /** @var  OptionDao */
    private $optionDao;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var callable[]  */
    public $onOrderCreated = array();

    /** @var callable[]  */
    public $onOrderUpdated = array();

    /**
     * @param OptionDao $optionDao
     */
    public function injectOptionDao(OptionDao $optionDao): void {
        $this->optionDao = $optionDao;
    }

    /**
     * @param $values array
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @return OrderEntity
     */
    public function createOrderFromOrderForm(array $values, ?EventEntity $event = null, ?EarlyEntity $early = null, ?SubstituteEntity $substitute = null) {
        $entityManager = $this->getEntityManager();
        $order = new OrderEntity();
        $order->setByValueArray($values);
        $order->setEarly($early);
        $order->setEvent($event);
        $order->setSubstitute($substitute);
        $entityManager->persist($order);
        $commonValues = $values['commons'];
        $hiddenAdditions = $this->additionDao->getHiddenEventAdditions($event);
        foreach ($values['children'] as $childValues) {
            $application = new ApplicationEntity();
            $application->setByValueArray($commonValues);
            $application->setByValueArray($childValues['child']);
            $application->setOrder($order);
            $entityManager->persist($application);
            foreach ($childValues['addittions'] as $additionId => $optionId) {
                $option = $this->optionDao->getOption($optionId);
                $choice = new ChoiceEntity();
                $choice->setOption($option);
                $choice->setApplication($application);
                $entityManager->persist($choice);
            }
            foreach ($hiddenAdditions as $hiddenAddition) {
                $options = $hiddenAddition->getOptions();
                for ($i = 0; $i < count($options) && $i < $hiddenAddition->getMinimum(); $i++) {
                    $option = $options[$i];
                    $choice = new ChoiceEntity();
                    $choice->setOption($option);
                    $choice->setApplication($application);
                    $entityManager->persist($choice);
                }
            }
        }
        $entityManager->flush();
        $this->onOrderCreated($order);
        return $order;
    }

    /**
     * @param $values array
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @return \App\Model\Persistence\Entity\OrderEntity
     */
    public function editOrderFromOrderForm($values, ?EventEntity $event = null, ?EarlyEntity $early = null, ?SubstituteEntity $substitute = null, ?OrderEntity $order = null) {
        $entityManager = $this->getEntityManager();
        //$order = new OrderEntity();
        $order->setByValueArray($values);
        $entityManager->persist($order);
        $commonValues = $values['commons'];
        $hiddenAdditions = $this->additionDao->getHiddenEventAdditions($event);
        foreach ($values['children'] as $id => $childValues) {
            foreach ($order->getApplications() as $application) {
                if ($application->getId() != $id) {
                    continue;
                }
                $application->setByValueArray($commonValues);
                $application->setByValueArray($childValues['child']);
                //$entityManager->persist($application);
                foreach ($childValues['addittions'] as $additionId => $optionId) {
                    foreach ($application->getChoices() as $choice) {
                        if ($choice->getOption()->getAddition()->getId() != $additionId) {
                            continue;
                        }
                        $option = $this->optionDao->getOption($optionId);
                        $choice->setOption($option);
                    }
                }
            }
        }
        $entityManager->flush();
        $this->onOrderUpdated($order);
        return $order;
    }
}