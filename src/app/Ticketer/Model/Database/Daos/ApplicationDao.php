<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class ApplicationDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return ApplicationEntity::class;
    }

    /**
     * @param CartEntity $cartEntity
     * @return IDataSource
     */
    public function getCartApplicationsGridModel(CartEntity $cartEntity): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where(['a.cart' => $cartEntity]);

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countOccupiedApplications(EventEntity $event): int
    {
        $states = ApplicationEntity::getStatesOccupied();

        return $this->getRepository()->count(
            [
                'cart.event.id' => $event->getId(),
                'state' => $states,
            ]
        );
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countIssuedApplications(EventEntity $event): int
    {
        $states = ApplicationEntity::getStatesNotIssued();

        return $this->getRepository()->count(
            [
                'event.id' => $event->getId(),
                'state !=' => $states,
            ]
        );
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countOccupiedApplicationsWithOption(OptionEntity $option): int
    {
        $states = ApplicationEntity::getStatesOccupied();

        return $this->getRepository()->count(
            [
                'choices.option.id' => $option->getId(),
                'state' => $states,
            ]
        );
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countIssuedApplicationsWithOption(OptionEntity $option): int
    {
        $states = ApplicationEntity::getStatesNotIssued();

        return $this->getRepository()->count(
            [
                'choices.option.id' => $option->getId(),
                'state !=' => $states,
            ]
        );
    }

    /**
     * @param EventEntity $event
     * @return IDataSource
     */
    public function getEventApplicationsGridModel(EventEntity $event): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where(
            [
                'a.event' => $event,
                //'a.state !=' => ApplicationEntity::getStatesNotIssued()
            ]
        );

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * @param EventEntity $event
     * @return ApplicationEntity[]
     */
    public function getAllEventApplications(EventEntity $event): array
    {
        return $this->getRepository()->findBy(
            [
                'event' => $event,
            ]
        );
    }

    public function getApplication(Uuid $id): ?ApplicationEntity
    {
        /** @var ApplicationEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param string[] $applicationIds
     * @param EventEntity $event
     * @return ApplicationEntity[]
     */
    public function getApplicationsForReservationDelegation(array $applicationIds, EventEntity $event): array
    {
        return $this->getRepository()->findBy(
            [
                'id IN' => $applicationIds,
                'state IN' => ApplicationEntity::getStatesReserved(),
                'event' => $event,
            ]
        );
    }

    /**
     * @param array<string> $ids
     * @return ApplicationEntity[]
     */
    public function getReservedApplications(EventEntity $eventEntity, array $ids = []): array
    {
        return $this->getRepository()->findBy(
            [
                'id IN' => $ids,
                'state IN' => ApplicationEntity::getStatesReserved(),
                'event' => $eventEntity,
            ]
        );
    }
}
