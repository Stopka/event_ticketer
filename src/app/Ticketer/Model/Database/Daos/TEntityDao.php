<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Doctrine\ORM\EntityRepository;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\EntityInterface;
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
     * @param Uuid $id
     * @return EntityInterface|null
     */
    protected function get(Uuid $id): ?EntityInterface
    {
        /**
         * @var EntityInterface|null $result
         */
        $result = $this->getEntityManager()->find($this->getEntityClass(), $id);

        return $result;
    }
}
