<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\Entities\AdministratorEntity;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class AdministratorFacade extends EntityFacade {

    protected function getEntityClass() {
        return AdministratorEntity::class;
    }


    /**
     * Najde administrátora podle id
     * @param int|NULL $id
     * @return AdministratorEntity|NULL
     */
    public function getAdministrator($id){
        return $this->get($id);
    }
}