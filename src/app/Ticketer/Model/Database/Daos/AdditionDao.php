<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Ticketer\Model\Database\Entities\AdditionVisibilityEntity;
use Ticketer\Model\Dtos\Uuid;
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
        $qb->where($qb->expr()->eq('a.event', ':event'))
            ->setParameters(
                new ArrayCollection(
                    [
                        new Parameter('event', $eventEntity->getId()->toString()),
                    ]
                )
            );

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * @param Uuid $id
     * @return AdditionEntity|null
     */
    public function getAddition(Uuid $id): ?AdditionEntity
    {
        /** @var AdditionEntity|null $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param EventEntity $event
     * @param callable(AdditionVisibilityEntity $visibility):bool $visibilityResolver
     * @return AdditionEntity[]
     */
    public function getEventAdditionsHiddenIn(EventEntity $event, callable $visibilityResolver): array
    {
        /** @var AdditionEntity[] $additions */
        $additions = $this->getRepository()->findBy(
            [
                'event' => $event,
            ]
        );
        $result = [];
        foreach ($additions as $addition) {
            if (!$addition->getVisibility()->matches($visibilityResolver)) {
                $result[] = $addition;
            }
        }

        return $result;
    }
}
