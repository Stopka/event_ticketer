<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 13:07
 */

namespace App\Model\Persistence\Dao;


use Kdyby\Doctrine\EntityManager;

trait TDoctrineEntityManager {

    /** @var  EntityManager */
    private $entityManager;

    /**
     * Dao constructor.
     * @param EntityManager $entityManager
     */
    public function injectEntityManager(EntityManager $entityManager): void {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager{
        return $this->entityManager;
    }
}