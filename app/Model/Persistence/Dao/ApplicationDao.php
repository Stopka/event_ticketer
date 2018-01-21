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
        $qb->whereCriteria(['a.cart' => $cartEntity]);
        return new Doctrine($qb);
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countOccupiedApplications(EventEntity $event): int {
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
            'event.id' => $event->getId(),
            'state !=' => $states
        ]);
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countOccupiedApplicationsWithOption(OptionEntity $option): int {
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
            'a.event' => $event,
            'a.state !=' => ApplicationEntity::getStatesNotIssued()
        ]);
        return new Doctrine($qb);
    }

    /**
     * @param  EventEntity $event
     * @return ApplicationEntity[]
     */
    public function getAllEventApplications(EventEntity $event): array {
        return $this->getRepository()->findBy([
            'event' => $event
        ]);
    }

    public function getApplication(?int $id): ?ApplicationEntity {
        /** @var ApplicationEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param string[] $applicationIds
     * @param EventEntity $event
     * @return ApplicationEntity[]
     */
    public function getApplicationsForReservationDelegation(array $applicationIds, EventEntity $event): array {
        return $this->getRepository()->findBy([
            'id IN' => $applicationIds,
            'state IN' => ApplicationEntity::getStatesReserved(),
            'event' => $event
        ]);
    }

}