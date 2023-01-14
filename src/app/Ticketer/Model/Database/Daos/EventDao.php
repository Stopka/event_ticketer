<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Ticketer\Model\Database\Enums\EventStateEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\EventEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class EventDao extends EntityDao
{
    protected function getEntityClass(): string
    {
        return EventEntity::class;
    }

    /**
     * @return EventEntity[]
     */
    public function getAllEvents(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @return IDataSource
     */
    public function getAllEventsGridModel(): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * Started and active events
     * @return EventEntity[]
     */
    public function getPublicAvailibleEvents(): array
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->eq('state', EventStateEnum::ACTIVE()),
                    Criteria::expr()->lte('startDate', new DateTimeImmutable()),
                )
            )
            ->orderBy(
                [
                    'startDate' => OrderEnum::ASC()->getValue(),
                ]
            );

        return $this->getRepository()
            ->matching($criteria)
            ->toArray();
    }

    /**
     * Started and future events
     * @return EventEntity[]
     */
    public function getPublicFutureEvents(): array
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->eq('state', EventStateEnum::ACTIVE()),
                    Criteria::expr()->gt('startDate', new DateTimeImmutable()),
                )
            )
            ->orderBy(
                [
                    'startDate' => OrderEnum::ASC()->getValue(),
                ]
            );

        return $this->getRepository()
            ->matching($criteria)
            ->toArray();
    }

    /**
     * @param Uuid $id
     * @return EventEntity|null
     */
    public function getEvent(Uuid $id): ?EventEntity
    {
        /** @var EventEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * Finds one active and started event if exists
     * @param Uuid $id
     * @return null|EventEntity
     */
    public function getPublicAvailibleEvent(Uuid $id): ?EventEntity
    {
        $event = $this->getEvent($id);
        if (null !== $event && $event->isPublicAvailible()) {
            return $event;
        }

        return null;
    }
}
