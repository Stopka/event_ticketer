<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\BaseEntity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

trait TEntityDao {
    /**
     * @return string class of entity
     */
    abstract protected function getEntityClass(): string;

    abstract protected function getEntityManager(): EntityManager;

    /**
     * @return EntityRepository
     */
    protected function getRepository(): EntityRepository {
        return $this->getEntityManager()->getRepository($this->getEntityClass());
    }

    /**
     * @param string|null $id
     * @return null|BaseEntity
     */
    protected function get(?string $id): ?BaseEntity {
        if (!isset($id)) {
            return NULL;
        }
        /** @var BaseEntity $result */
        $result = $this->getEntityManager()->find($this->getEntityClass(), $id);
        return $result;
    }
}