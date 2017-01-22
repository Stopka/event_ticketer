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
use Grido\DataSources\Doctrine;

class ApplicationFacade extends EntityFacade {

    protected function getEntityClass() {
        return ApplicationEntity::class;
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
            'options.id'=>$option->getId(),
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
            'options.id'=>$option->getId(),
            'state !='=>$states
        ]);
    }

    /**
     * @return Doctrine
     */
    public function getAllApplicationsGridModel(){
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->addSelect('a')
            ->where($qb->expr()->notIn('a.state',ApplicationEntity::getStatesNotIssued()));
        return new Doctrine($qb);
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