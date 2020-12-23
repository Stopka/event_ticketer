<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

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
        return $this->getRepository()->findBy(
            [
                'state' => EventEntity::STATE_ACTIVE,
                'startDate <=' => new \DateTime(),
            ],
            [
                'startDate' => OrderEnum::ASC()->getValue(),
            ]
        );
    }

    /**
     * Started and future events
     * @return EventEntity[]
     */
    public function getPublicFutureEvents(): array
    {
        return $this->getRepository()->findBy(
            [
                'state' => EventEntity::STATE_ACTIVE,
                'startDate >' => new \DateTime(),
            ],
            [
                'startDate' => OrderEnum::ASC()->getValue(),
            ]
        );
    }

    /**
     * @param int|null $id
     * @return EventEntity|null
     */
    public function getEvent(?int $id): ?EventEntity
    {
        /** @var EventEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * Finds one active and started event if exists
     * @param int|null $id
     * @return null|EventEntity
     */
    public function getPublicAvailibleEvent(?int $id): ?EventEntity
    {
        $event = $this->getEvent($id);
        if (null !== $event && $event->isPublicAvailible()) {
            return $event;
        }

        return null;
    }
}
