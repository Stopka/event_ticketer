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

class AdministratorFacade extends BaseFacade {

    /**
     * Najde administrátora podle id
     * @param int|NULL $id
     * @return AdministratorEntity|NULL
     */
    public function getAdministrator($id){
        if(!isset($id)){
            return NULL;
        }
        return $this->getEntityManager()->find(AdministratorEntity::class, $id);
    }
}