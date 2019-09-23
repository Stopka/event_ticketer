<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 13:32
 */

namespace App\Model;


use App\Model\Persistence\Dao\AdministratorDao;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\SmartObject;

class AdminAuthenticator implements IAuthenticator {
    use SmartObject;

    /** @var  \App\Model\Persistence\Dao\AdministratorDao */
    private $administratorDao;

    public function __construct(AdministratorDao $administratorDao) {
        $this->administratorDao = $administratorDao;
    }


    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username,$password) = $credentials;
        $admin = $this->administratorDao->getAdministratorByUsername($username);
        if (!$admin) {
            throw new AuthenticationException('Admin does not exist');
        }
        if (!$admin->verifyPassword($password)) {
            throw new AuthenticationException('Wrong password');
        }
        return new Identity($admin->getId(), 'administrator', $admin->getValueArray());
    }
}