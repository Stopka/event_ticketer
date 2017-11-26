<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Entity\OrderEntity;
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class ApplicationDao extends EntityDao {

    protected function getEntityClass(): string {
        return ApplicationEntity::class;
    }

    /**
     * @param OrderEntity $order
     * @return IDataSource
     */
    public function getOrderApplicationsGridModel(OrderEntity $order): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->addSelect('a')
            ->where($qb->expr()->eq('a.order', $order->getId()
            ));
        return new Doctrine($qb);
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countReservedApplications(EventEntity $event): int {
        $states = ApplicationEntity::getStatesReserved();
        return $this->getRepository()->countBy([
            'order.event.id' => $event->getId(),
            'state' => $states
        ]);
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countIssuedApplications(EventEntity $event): int {
        $states = ApplicationEntity::getStatesNotIssued();
        return $this->getRepository()->countBy([
            'order.event.id' => $event->getId(),
            'state !=' => $states
        ]);
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countReservedApplicationsWithOption(OptionEntity $option): int {
        $states = ApplicationEntity::getStatesReserved();
        return $this->getRepository()->countBy([
            'choices.option.id' => $option->getId(),
            'state' => $states
        ]);
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countIssuedApplicationsWithOption(OptionEntity $option): int {
        $states = ApplicationEntity::getStatesNotIssued();
        return $this->getRepository()->countBy([
            'choices.option.id' => $option->getId(),
            'state !=' => $states
        ]);
    }

    /**
     * @return Doctrine
     */
    public function getAllApplicationsGridModel(EventEntity $event): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->addSelect('a')
            ->where($qb->expr()->andX(
                $qb->expr()->notIn('a.state', ApplicationEntity::getStatesNotIssued())/*,
                $qb->expr()->eq('a.order.event.id',$event->getId())*/
            ));
        //TODO filtr podle eventu
        return new Doctrine($qb);
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getAllEventApplications(EventEntity $event): array {

        //TODO filtr podle eventu
        return $this->getRepository()->findAll();
    }

    public function getApplication(?string $id): ?ApplicationEntity {
        /** @var ApplicationEntity $result */
        $result = $this->get($id);
        return $result;
    }

}