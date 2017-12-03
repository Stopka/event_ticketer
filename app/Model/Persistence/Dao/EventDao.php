<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\EventEntity;
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class EventDao extends EntityDao {

    protected function getEntityClass(): string {
        return EventEntity::class;
    }

    /**
     * @return EventEntity[]
     */
    public function getAllEvents(): array {
        return $this->getRepository()->findAll();
    }

    /**
     * @return IDataSource
     */
    public function getAllEventsGridModel(): IDataSource{
        $qb = $this->getRepository()->createQueryBuilder('a');
        return new Doctrine($qb);
    }

    /**
     * Started and active events
     * @return EventEntity[]
     */
    public function getPublicAvailibleEvents(): array {
        return $this->getRepository()->findBy([
            'state' => EventEntity::STATE_ACTIVE,
            'startDate <=' => new \DateTime()
        ], [
            'startDate' => self::ORDER_ASC
        ]);
    }

    /**
     * Started and future events
     * @return EventEntity[]
     */
    public function getPublicFutureEvents(): array {
        return $this->getRepository()->findBy([
            'state' => EventEntity::STATE_ACTIVE,
            'startDate >' => new \DateTime()
        ], [
            'startDate' => self::ORDER_ASC
        ]);
    }

    /**
     * @param null|string $id
     * @return EventEntity|null
     */
    public function getEvent(?string $id): ?EventEntity{
        /** @var EventEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * Finds one active and started event if exists
     * @param string $id
     * @return null|EventEntity
     */
    public function getPublicAvailibleEvent(?string $id): ?EventEntity {
        $event = $this->getEvent($id);
        if ($event && $event->isPublicAvailible()) {
            return $event;
        }
        return NULL;
    }
}