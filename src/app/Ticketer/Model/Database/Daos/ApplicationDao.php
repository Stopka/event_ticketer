<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Parameter;
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
        $qb->where(
            $qb->expr()->eq('a.cart', ':cart')
        )
            ->setParameters(
                new ArrayCollection(
                    [
                        new Parameter('cart', $cartEntity),
                    ]
                )
            );

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countOccupiedApplications(EventEntity $event): int
    {
        $states = ApplicationStateEnum::listOccupied();

        $qb = $this->getRepository()->createQueryBuilder('a')
            ->innerJoin('a.cart', 'c');
        $qb->select('COUNT(a)');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('c.event', ':event'),
                $qb->expr()->in('a.state', $states)
            )
        );
        $qb->setParameters(
            new ArrayCollection(
                [
                    new Parameter('event', $event),
                ]
            )
        );

        return (int)$qb->getQuery()->getSingleScalarResult();
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
                    Criteria::expr()->eq('event', $event),
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
        $qb = $this->getRepository()->createQueryBuilder('a')
            ->innerJoin('a.choices', 'c');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('c.option', ':option'),
                $qb->expr()->in('a.state', $states)
            )
        );
        $qb->setParameters(
            new ArrayCollection(
                [
                    new Parameter('option', $option),
                ]
            )
        );
        $qb->select('COUNT(a)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param OptionEntity $option
     * @return integer
     */
    public function countIssuedApplicationsWithOption(OptionEntity $option): int
    {
        $states = ApplicationStateEnum::listNotIssued();
        $qb = $this->getRepository()->createQueryBuilder('a')
            ->innerJoin('a.choices', 'c');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('c.option', ':option'),
                $qb->expr()->notIn('a.state', $states)
            )
        );
        $qb->setParameters(
            new ArrayCollection(
                [
                    new Parameter('option', $option),
                ]
            )
        );
        $qb->select('COUNT(a)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param EventEntity $event
     * @return IDataSource
     */
    public function getEventApplicationsGridModel(EventEntity $event): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where(
            $qb->expr()->eq('a.event', ':event')
        )
            ->setParameters(
                new ArrayCollection(
                    [
                        new Parameter('event', $event),
                    ]
                )
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
