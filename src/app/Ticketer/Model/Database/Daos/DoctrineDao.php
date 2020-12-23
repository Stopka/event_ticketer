<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;

abstract class DoctrineDao extends Dao
{
    use TDoctrineEntityManager;

    public function __construct(EntityManagerWrapper $entityManager)
    {
        $this->injectEntityManager($entityManager);
    }
}
