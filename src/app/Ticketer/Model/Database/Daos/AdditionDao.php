<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class AdditionDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return AdditionEntity::class;
    }

    /**
     * @param EventEntity $eventEntity
     * @return IDataSource
     */
    public function getEventAdditionsGridModel(EventEntity $eventEntity): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where(['a.event' => $eventEntity]);

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * @param null|int $id
     * @return AdditionEntity|null
     */
    public function getAddition(?int $id): ?AdditionEntity
    {
        /** @var AdditionEntity|null $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param EventEntity $event
     * @param string $place
     * @return AdditionEntity[]
     */
    public function getEventAdditionsHiddenIn(EventEntity $event, string $place): array
    {
        /** @var AdditionEntity[] $additions */
        $additions = $this->getRepository()->findBy(
            [
                'event.id' => $event->getId(),
            ]
        );
        $result = [];
        foreach ($additions as $addition) {
            if (!$addition->isVisibleIn($place)) {
                $result[] = $addition;
            }
        }

        return $result;
    }
}
