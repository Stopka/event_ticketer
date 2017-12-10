<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;


use App\Model\Facades\EntityFacade;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use App\Model\Persistence\Entity\EventEntity;

class EarlyWaveDao extends EntityDao {

    protected function getEntityClass(): string {
        return EarlyWaveEntity::class;
    }

    public function getEarlyWave(?string $id): ?EarlyWaveEntity{
        /** @var EarlyWaveEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @return EarlyWaveEntity[]
     */
    public function getUnsentInviteEarlyWaves(): array {
        return $this->getRepository()->findBy([
            'event.state' => EventEntity::STATE_ACTIVE,
            'startDate <=' => new \DateTime(),
            'inviteSent' => false
        ]);
    }

    /**
     * @param EventEntity|null $eventEntity
     * @return EarlyWaveEntity[]
     */
    public function getEventEearlyWaves(?EventEntity $eventEntity): array{
        return $this->getRepository()->findBy([
            'event' => $eventEntity
        ]);
    }

}