<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

abstract class EntityDao extends DoctrineDao
{
    use TEntityDao;
}
