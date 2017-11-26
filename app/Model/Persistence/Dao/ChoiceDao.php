<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;

use App\Model\Exception\ApplicationException;
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

    /**
     * @param $choiceId integer
     * @return null|ChoiceEntity
     */
    public function inversePayed(?string $choiceId): void {
        $choice = $this->getChoice($choiceId);
        if (!$choice) {
            throw new ApplicationException("Choice not found");
        }
        $choice->inversePayed();
        $this->getEntityManager()->flush();
    }

}