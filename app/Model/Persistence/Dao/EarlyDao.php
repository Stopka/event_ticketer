<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class EarlyDao extends EntityDao {

    protected function getEntityClass(): string {
        return EarlyEntity::class;
    }

    /**
     * @param null|int $id
     * @return EarlyEntity|null
     */
    public function getEarly(?int $id): ?EarlyEntity {
        /** @var EarlyEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param string $uid
     * @return EarlyEntity|null
     */
    public function getReadyEarlyByUid(string $uid): ?EarlyEntity {
        $early = $this->getRepository()->findOneBy(['uid' => $uid]);
        if ($early && $early->isReadyToRegister()) {
            return $early;
        }
        return NULL;
    }

    /**
     * @param EventEntity|null $eventEntity
     * @return IDataSource
     */
    public function getEventEarliesGridModel(?EventEntity $eventEntity): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->whereCriteria(['a.earlyWave.event' => $eventEntity]);
        return new Doctrine($qb);
    }

}