<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Persistence\Dao\AdministratorDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdministratorEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

class AdministratorManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  AdministratorDao */
    private $administratorDao;

    /**
     * EventManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManagerWrapper $entityManager, AdministratorDao $administratorDao) {
        $this->injectEntityManager($entityManager);
        $this->administratorDao = $administratorDao;
    }

    /**
     * @param string $username
     * @param string $password
     * @return AdministratorEntity|null
     * @throws \Exception
     */
    public function checkFirstAdministrator(string $username, string $password): ?AdministratorEntity {
        $count = $this->administratorDao->countAdministrators();
        if (!$count) {
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