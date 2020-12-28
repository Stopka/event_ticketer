<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\Enums\SubstituteStateEnum;
use Ticketer\Model\Dtos\Uuid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class SubstituteDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return SubstituteEntity::class;
    }

    /**
     * @param EventEntity $event
     * @return IDataSource
     */
    public function getAllSubstitutesGridModel(EventEntity $event): IDataSource
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('s');
        $queryBuilder->where(['s.event' => $event]);

        return new DoctrineDataSource($queryBuilder, 'id');
    }

    public function getSubstitute(Uuid $id): ?SubstituteEntity
    {
        /** @var SubstituteEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @param string $uid
     * @return SubstituteEntity|null
     */
    public function getReadySubstituteByUid(string $uid): ?SubstituteEntity
    {
        /** @var SubstituteEntity|null $substitute */
        $substitute = $this->getRepository()->findOneBy(['uid' => $uid]);
        if (null === $substitute || !$substitute->isActive()) {
            return null;
        }

        return $substitute;
    }

    /**
     * @return SubstituteEntity[]
     */
    public function getOverdueSubstitutesReadyToUpdateState(): array
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->andX(
                    Criteria::expr()->eq('state', SubstituteStateEnum::ACTIVE()),
                    Criteria::expr()->lt('endDate', new DateTimeImmutable())
                )
            );

        return $this->getRepository()->matching($criteria)->toArray();
    }
}
