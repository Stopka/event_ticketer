<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\EarlyEntity;

class EarlyDao extends EntityDao {

    protected function getEntityClass(): string {
        return EarlyEntity::class;
    }

    /**
     * @param null|string $id
     * @return EarlyEntity|null
     */
    public function getEarly(?string $id): ?EarlyEntity {
        /** @var EarlyEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param null|string $id
     * @return EarlyEntity|null
     */
    public function getReadyEarly(?string $id): ?EarlyEntity {
        $early = $this->getEarly($id);
        if ($early && $early->isReadyToRegister()) {
            return $early;
        }
        return NULL;
    }

}