<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Dtos\Uuid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class EarlyDao extends EntityDao
{
    protected function getEntityClass(): string
    {
        return EarlyEntity::class;
    }

    /**
     * @param Uuid $id
     * @return EarlyEntity|null
     */
    public function getEarly(Uuid $id): ?EarlyEntity
    {
        /** @var EarlyEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param Uuid $id
     * @return EarlyEntity|null
     */
    public function getReadyEarly(Uuid $id): ?EarlyEntity
    {
        $early = $this->getEarly($id);
        if (null !== $early && $early->isReadyToRegister()) {
            return $early;
        }

        return null;
    }

    /**
     * @param EventEntity|null $eventEntity
     * @return IDataSource
     */
    public function getEventEarliesGridModel(?EventEntity $eventEntity): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('e')
            ->innerJoin('e.earlyWave', 'ew');
        $qb->where(
            $qb->expr()->eq('ew.event', ':event')
        );
        $qb->setParameters(
            new ArrayCollection(
                [
                    new Parameter('event', $eventEntity),
                ]
            )
        );

        return new DoctrineDataSource($qb, 'id');
    }
}
