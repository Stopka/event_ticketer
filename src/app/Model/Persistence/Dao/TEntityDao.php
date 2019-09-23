<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\BaseEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Kdyby\Doctrine\EntityRepository;

trait TEntityDao {
    /**
     * @return string class of entity
     */
    abstract protected function getEntityClass(): string;

    abstract protected function getEntityManager(): EntityManagerWrapper;

    /**
     * @return EntityRepository
     */
    protected function getRepository(): EntityRepository {
        return $this->getEntityManager()->getRepository($this->getEntityClass());
    }

    /**
     * @param int|null $id
     * @return BaseEntity|null
     * @throws \App\Model\Exception\ORMException
     */
    protected function get(?int $id): ?BaseEntity {
        if (!isset($id)) {
            return NULL;
        }
        /** @var BaseEntity $result */
        $result = $this->getEntityManager()->find($this->getEntityClass(), $id);
        return $result;
    }
}