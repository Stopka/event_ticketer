<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Persistence\Dao;


use App\Model\Facades\EntityFacade;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;

class OptionDao extends EntityDao {

    protected function getEntityClass(): string {
        return OptionEntity::class;
    }

    /**
     * @param null|string $id
     * @return OptionEntity|null
     */
    public function getOption(?string $id): ?OptionEntity{
        /** @var OptionEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param EventEntity $event
     * @return OptionEntity[]
     */
    public function getOptionsWithLimitedCapacity(EventEntity $event): array {
        return $this->getRepository()->findBy([
            'addition.event.id' => $event->getId(),
            'capacity !=' => null
        ]);
    }

}