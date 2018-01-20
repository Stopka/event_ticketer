<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class SubstituteDao extends EntityDao {

    protected function getEntityClass(): string {
        return SubstituteEntity::class;
    }

    /**
     * @param EventEntity $event
     * @return IDataSource
     */
    public function getAllSubstitutesGridModel(EventEntity $event): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('s');
        $qb->whereCriteria(['s.event' => $event]);
        return new Doctrine($qb);
    }

    public function getSubstitute(?int $id): ?SubstituteEntity {
        /** @var SubstituteEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param string $uid
     * @return SubstituteEntity|null
     */
    public function getReadySubstituteByUid(string $uid): ?SubstituteEntity {
        $substitute = $this->getRepository()->findOneBy(['uid' => $uid]);
        if (!$substitute || !$substitute->isActive()) {
            return NULL;
        }
        return $substitute;
    }
}