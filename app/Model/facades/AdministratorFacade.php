<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\Persistence\Entity\AdministratorEntity;

class AdministratorFacade extends EntityFacade {

    protected function getEntityClass() {
        return AdministratorEntity::class;
    }

    /**
     * Najde administrátora podle id
     * @param integer|NULL $id
     * @return \App\Model\Persistence\Entity\AdministratorEntity|NULL
     */
    public function getAdministrator($id){
        return $this->get($id);
    }

    /**
     * Najde administrátora podle uživatelského jména
     * @param string|NULL $username
     * @return \App\Model\Persistence\Entity\AdministratorEntity|NULL
     */
    public function getAdministratorByUsername($username){
        return $this->getRepository()->findOneBy([
            'username' => $username
        ]);
    }
}