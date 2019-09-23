<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 13:07
 */

namespace App\Model\Persistence\Dao;


use App\Model\Persistence\EntityManagerWrapper;
use Kdyby\Doctrine\EntityManager;

trait TDoctrineEntityManager {

    /** @var  EntityManagerWrapper */
    private $entityManager;

    /**
     * Dao constructor.
     * @param EntityManagerWrapper $entityManager
     */
    protected function injectEntityManager(EntityManagerWrapper $entityManager): void {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManagerWrapper {
        return $this->entityManager;
    }
}