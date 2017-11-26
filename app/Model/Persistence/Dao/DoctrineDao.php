<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;


use Kdyby\Doctrine\EntityManager;

abstract class DoctrineDao extends Dao {
    use TDoctrineEntityManager;

    public function __construct(EntityManager $entityManager) {
        $this->injectEntityManager($entityManager);
    }
}