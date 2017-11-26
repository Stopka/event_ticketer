<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;


use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\EventEntity;

class AdditionDao extends EntityDao {

    protected function getEntityClass(): string {
        return AdditionEntity::class;
    }

    /**
     * @param null|string $id
     * @return AdditionEntity|null
     */
    public function getAddition(?string $id): ?AdditionEntity {
        /** @var AdditionEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param EventEntity $event
     * @return AdditionEntity[]
     */
    public function getHiddenEventAdditions(EventEntity $event): array {
        return $this->getRepository()->findBy([
            'visible' => false,
            'event.id' => $event->getId()]
        );
    }

}