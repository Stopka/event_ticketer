<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Database\Daos\AdministratorDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\AdministratorEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;

class AdministratorManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  AdministratorDao */
    private $administratorDao;

    /**
     * EventManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param AdministratorDao $administratorDao
     */
    public function __construct(EntityManagerWrapper $entityManager, AdministratorDao $administratorDao)
    {
        $this->injectEntityManager($entityManager);
        $this->administratorDao = $administratorDao;
    }

    /**
     * @param string $username
     * @param string $password
     * @return AdministratorEntity|null
     * @throws \Exception
     */
    public function checkFirstAdministrator(string $username, string $password): ?AdministratorEntity
    {
        $count = $this->administratorDao->countAdministrators();
        if (0 === $count) {
            $administrator = new AdministratorEntity();
            $administrator->setUsername($username);
            $administrator->setPassword($password);
            $em = $this->getEntityManager();
            $em->persist($administrator);
            $em->flush();

            return $administrator;
        }

        return null;
    }
}
