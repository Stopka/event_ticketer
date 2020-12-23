<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\EntityManager;

trait TDoctrineEntityManager
{

    /** @var  EntityManager */
    private $entityManager;

    /**
     * Dao constructor.
     * @param EntityManager $entityManager
     */
    protected function injectEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
