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
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class AdditionDao extends EntityDao {

    protected function getEntityClass(): string {
        return AdditionEntity::class;
    }

    /**
     * @param EventEntity $eventEntity
     * @return IDataSource
     */
    public function getEventAdditionsGridModel(EventEntity $eventEntity): IDataSource {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->whereCriteria(['a.event'=>$eventEntity]);
        return new Doctrine($qb);
    }

    /**
     * @param null|int $id
     * @return AdditionEntity|null
     */
    public function getAddition(?int $id): ?AdditionEntity {
        /** @var AdditionEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @param EventEntity $event
     * @param string $place
     * @return AdditionEntity[]
     */
    public function getEventAdditionsHiddenIn(EventEntity $event, string $place): array {
        /** @var AdditionEntity[] $additions */
        $additions = $this->getRepository()->findBy([
                'event.id' => $event->getId()
            ]);
        $result = [];
        foreach ($additions as $addition){
            if(!$addition->isVisibleIn($place)){
                $result[] = $addition;
            }
        }
        return $result;
    }

}