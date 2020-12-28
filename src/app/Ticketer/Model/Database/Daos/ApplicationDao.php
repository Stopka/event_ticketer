<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\Common\Collections\Criteria;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
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
        $states = ApplicationStateEnum::listOccupied();
        $criteria = Criteria::create()->where(
            Criteria::expr()->andX(
                Criteria::expr()->eq('cart.event', $event),
                Criteria::expr()->in('state', $states)
            )
        );

        return $this->getRepository()->matching($criteria)->count();
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countIssuedApplications(EventEntity $event): int
    {
        $states = ApplicationStateEnum::listNotIssued();
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->eq('event.id', $event->getId()),
                    Criteria::expr()->notIn('state', $states)
                )
            );

        return $this->getRepository()->matching($criteria)->count();
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countOccupiedApplicationsWithOption(OptionEntity $option): int
    {
        $states = ApplicationStateEnum::listOccupied();
        $criteria = Criteria::create()->where(
            Criteria::expr()->andX(
                Criteria::expr()->eq('choices.option', $option),
                Criteria::expr()->in('state', $states)
            )
        );

        return $this->getRepository()->matching($criteria)->count();
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countIssuedApplicationsWithOption(OptionEntity $option): int
    {
        $states = ApplicationStateEnum::listNotIssued();
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->eq('choices.option.id', $option->getId()),
                    Criteria::expr()->notIn('state', $states)
                )
            );

        return $this->getRepository()->matching($criteria)->count();
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
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->in('id', $applicationIds),
                    Criteria::expr()->in('state', ApplicationStateEnum::listReserved()),
                    Criteria::expr()->eq('event', $event)
                )
            );

        return $this->getRepository()->matching($criteria)->toArray();
    }

    /**
     * @param EventEntity $eventEntity
     * @param array<string> $ids
     * @return ApplicationEntity[]
     */
    public function getReservedApplications(EventEntity $eventEntity, array $ids = []): array
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->in('id', $ids),
                    Criteria::expr()->in('state', ApplicationStateEnum::listReserved()),
                    Criteria::expr()->eq('event', $eventEntity)
                )
            );

        return $this->getRepository()->matching($criteria)->toArray();
    }
}
