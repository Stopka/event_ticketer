<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\ORM\EntityRepository;
use Ticketer\Model\Database\Entities\IEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Ticketer\Model\Exceptions\ORMException;

/**
 * Trait TEntityDao
 * @package          Ticketer\Model\Database\Daos
 */
trait TEntityDao
{
    /**
     * @return string
     * @phpstan-return class-string
     */
    abstract protected function getEntityClass(): string;

    abstract protected function getEntityManager(): EntityManagerWrapper;

    /**
     * @return EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        /** @var EntityRepository $repository */
        $repository = $this->getEntityManager()->getRepository($this->getEntityClass());

        return $repository;
    }

    /**
     * @param int|null $id
     * @return IEntity|null
     * @throws ORMException
     */
    protected function get(?int $id): ?IEntity
    {
        if (!isset($id)) {
            return null;
        }
        /**
         * @var IEntity $result
         */
        $result = $this->getEntityManager()->find($this->getEntityClass(), $id);

        return $result;
    }
}
