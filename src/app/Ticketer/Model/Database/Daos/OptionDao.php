<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class OptionDao extends EntityDao
{
    protected function getEntityClass(): string
    {
        return OptionEntity::class;
    }

    /**
     * @param Uuid $id
     * @return OptionEntity|null
     */
    public function getOption(Uuid $id): ?OptionEntity
    {
        /** @var OptionEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param EventEntity $event
     * @return OptionEntity[]
     */
    public function getOptionsWithLimitedCapacity(EventEntity $event): array
    {
        $qb = $this->getRepository()->createQueryBuilder('o')
            ->join('o.addition', 'a');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('a.event', ':event'),
                $qb->expr()->isNotNull('o.capacity')
            )
        );
        $qb->setParameters(
            new ArrayCollection(
                [
                    new Parameter('event', $event),
                ]
            )
        );

        return $qb->getQuery()->getResult();
    }

    public function getAdditionOptionsGridModel(AdditionEntity $additionEntity): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where($qb->expr()->eq('a.addition', ':addition'))
            ->setParameters(
                new ArrayCollection(
                    [
                        new Parameter('addition', $additionEntity->getId()->toString()),
                    ]
                )
            );

        return new DoctrineDataSource($qb, 'id');
    }
}
