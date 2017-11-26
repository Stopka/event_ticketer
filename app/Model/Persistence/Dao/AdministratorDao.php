<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\AdministratorEntity;

class AdministratorDao extends EntityDao {

    protected function getEntityClass(): string {
        return AdministratorEntity::class;
    }

    /**
     * Najde administrátora podle id
     * @param integer|NULL $id
     * @return AdministratorEntity|NULL
     */
    public function getAdministrator(?string $id): ?AdministratorEntity {
        /** @var AdministratorEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * Najde administrátora podle uživatelského jména
     * @param string|NULL $username
     * @return AdministratorEntity|NULL
     */
    public function getAdministratorByUsername(?string $username): ?AdministratorEntity {
        return $this->getRepository()->findOneBy([
            'username' => $username
        ]);
    }
}