<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\Entities\AdministratorEntity;

class AdministratorFacade extends EntityFacade {

    protected function getEntityClass() {
        return AdministratorEntity::class;
    }

    /**
     * Najde administrátora podle id
     * @param integer|NULL $id
     * @return AdministratorEntity|NULL
     */
    public function getAdministrator($id){
        return $this->get($id);
    }

    /**
     * Najde administrátora podle uživatelského jména
     * @param string|NULL $username
     * @return AdministratorEntity|NULL
     */
    public function getAdministratorByUsername($username){
        return $this->getRepository()->findOneBy([
            'username' => $username
        ]);
    }
}