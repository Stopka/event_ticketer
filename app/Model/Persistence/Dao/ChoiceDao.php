<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\ChoiceEntity;

class ChoiceDao extends EntityDao {

    protected function getEntityClass(): string {
        return ChoiceEntity::class;
    }

    public function getChoice(?string $id): ?ChoiceEntity {
        /** @var ChoiceEntity $result */
        $result = $this->get($id);
        return $result;
    }

}