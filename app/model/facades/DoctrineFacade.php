<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;

use Kdyby\Doctrine\EntityManager;

class DoctrineFacade extends BaseFacade {

    /** @var  EntityManager */
    private $entityManager;

    /**
     * BaseFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(){
        return $this->entityManager;
    }

}