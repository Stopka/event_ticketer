<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\ApplicationEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OptionEntity;
use App\Model\Entities\OrderEntity;
use Grido\DataSources\Doctrine;

class ApplicationFacade extends EntityFacade {

    protected function getEntityClass() {
        return ApplicationEntity::class;
    }

    public function getOrderApplicationsGridModel(OrderEntity $order){
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->addSelect('a')
            ->where($qb->expr()->eq('a.order',$order->getId()
            ));
        return new Doctrine($qb);
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countReservedApplications(EventEntity $event){
        $states = ApplicationEntity::getStatesReserved();
        return $this->getRepository()->countBy([
            'order.event.id'=>$event->getId(),
            'state'=>$states
        ]);
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countIssuedApplications(EventEntity $event){
        $states = ApplicationEntity::getStatesNotIssued();
        return $this->getRepository()->countBy([
            'order.event.id'=>$event->getId(),
            'state !='=>$states
        ]);
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countReservedApplicationsWithOption(OptionEntity $option){
        $states = ApplicationEntity::getStatesReserved();
        return $this->getRepository()->countBy([
            'choices.option.id'=>$option->getId(),
            'state'=>$states
        ]);
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countIssuedApplicationsWithOption(OptionEntity $option){
        $states = ApplicationEntity::getStatesNotIssued();
        return $this->getRepository()->countBy([
            'choices.option.id'=>$option->getId(),
            'state !='=>$states
        ]);
    }

    /**
     * @return Doctrine
     */
    public function getAllApplicationsGridModel(EventEntity $event){
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->addSelect('a')
            ->where($qb->expr()->andX(
                $qb->expr()->notIn('a.state',ApplicationEntity::getStatesNotIssued())/*,
                $qb->expr()->eq('a.order.event.id',$event->getId())*/
            ));
        //TODO filtr podle eventu
        return new Doctrine($qb);
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getAllEventApplications(EventEntity $event){

        //TODO filtr podle eventu
        return $this->getRepository()->findAll();
    }

    /**
     * @param $key string
     * @param $id integer
     * @return null|ApplicationEntity
     */
    public function inverseValue($key, $id){
        /** @var ApplicationEntity $application */
        $application = $this->get($id);
        switch ($key){
            case 'deposited':
                $application->setDeposited(!$application->isDeposited());
                break;
            case 'payed':
                $application->setPayed(!$application->isPayed());
                break;
            case 'signed':
                $application->setSigned(!$application->isSigned());
                break;
            case 'invoiced':
                $application->setInvoiced(!$application->isInvoiced());
                break;
        }
        $this->getEntityManager()->flush();
    }

}