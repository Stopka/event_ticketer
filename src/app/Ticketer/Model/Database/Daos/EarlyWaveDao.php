<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use DateTimeImmutable;
use Ticketer\Model\Database\Enums\EventStateEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class EarlyWaveDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return EarlyWaveEntity::class;
    }

    public function getEarlyWave(Uuid $id): ?EarlyWaveEntity
    {
        /** @var EarlyWaveEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @return EarlyWaveEntity[]
     */
    public function getUnsentInviteEarlyWaves(): array
    {
        $qb = $this->getRepository()->createQueryBuilder('ew')
            ->innerJoin('ew.event', 'e');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('e.state', EventStateEnum::ACTIVE()),
                $qb->expr()->lte('ew.startDate', new DateTimeImmutable()),
                $qb->expr()->eq('ew.inviteSent', false)
            )
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param EventEntity|null $eventEntity
     * @return EarlyWaveEntity[]
     */
    public function getEventEearlyWaves(?EventEntity $eventEntity): array
    {
        return $this->getRepository()->findBy(
            [
                'event' => $eventEntity,
            ]
        );
    }

    /**
     * @param EventEntity|null $eventEntity
     * @return IDataSource
     */
    public function getEventEarlyWavesGridModel(?EventEntity $eventEntity): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('ew');
        $qb->where(
            $qb->expr()->eq('ew.event', ':event')
        );
        $qb->setParameters(
            [
                'event' => $eventEntity,
            ]
        );

        return new DoctrineDataSource($qb, 'id');
    }
}
