<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\AdministratorEntity;

class AdministratorDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return AdministratorEntity::class;
    }

    /**
     * Najde administrátora podle id
     * @param integer|NULL $id
     * @return AdministratorEntity|NULL
     */
    public function getAdministrator(?int $id): ?AdministratorEntity
    {
        /** @var AdministratorEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * Najde administrátora podle uživatelského jména
     * @param string|NULL $username
     * @return AdministratorEntity|null
     */
    public function getAdministratorByUsername(?string $username): ?AdministratorEntity
    {
        /** @var AdministratorEntity|null $administrator */
        $administrator = $this->getRepository()->findOneBy(
            [
                'username' => $username,
            ]
        );

        return $administrator;
    }

    public function countAdministrators(): int
    {
        return $this->getRepository()->count([]);
    }
}
