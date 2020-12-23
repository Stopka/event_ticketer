<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class EarlyDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return EarlyEntity::class;
    }

    /**
     * @param null|int $id
     * @return EarlyEntity|null
     */
    public function getEarly(?int $id): ?EarlyEntity
    {
        /** @var EarlyEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param string $uid
     * @return EarlyEntity|null
     */
    public function getReadyEarlyByUid(string $uid): ?EarlyEntity
    {
        /** @var EarlyEntity|null $early */
        $early = $this->getRepository()->findOneBy(['uid' => $uid]);
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
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where(['a.earlyWave.event' => $eventEntity]);

        return new DoctrineDataSource($qb, 'id');
    }
}
