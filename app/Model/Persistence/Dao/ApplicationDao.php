<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class ApplicationDao extends EntityDao {

    protected function getEntityClass(): string {
        return ApplicationEntity::class;
    }

    /**
     * @param CartEntity $cartEntity
     * @return IDataSource
     */
    public function getCartApplicationsGridModel(CartEntity $cartEntity): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->whereCriteria(['a.cart'=>$cartEntity]);
        return new Doctrine($qb);
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countReservedApplications(EventEntity $event): int {
        $states = ApplicationEntity::getStatesOccupied();
        return $this->getRepository()->countBy([
            'cart.event.id' => $event->getId(),
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
            'cart.event.id' => $event->getId(),
            'state !=' => $states
        ]);
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countReservedApplicationsWithOption(OptionEntity $option): int {
        $states = ApplicationEntity::getStatesOccupied();
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
     * @param EventEntity $event
     * @return IDataSource
     */
    public function getEventApplicationsGridModel(EventEntity $event): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->whereCriteria([
            'a.cart.event'=>$event,
            'a.state !='=>ApplicationEntity::getStatesNotIssued()
        ]);
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