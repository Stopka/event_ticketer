<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 13:32
 */

namespace App\Model;


use App\Model\Persistence\Dao\AdministratorDao;
use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;

class AdminAuthenticator extends Object implements IAuthenticator {

    /** @var  \App\Model\Persistence\Dao\AdministratorDao */
    private $administratorFacade;

    public function __construct(AdministratorDao $administratorFacade) {
        $this->administratorFacade = $administratorFacade;
    }


    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username,$password) = $credentials;
        $admin = $this->administratorFacade->getAdministratorByUsername($username);
        if(!$admin||!$admin->verifyPassword($password)){
            throw new AuthenticationException('Neplatné přihlašovací údaje');
        }
        return new Identity($admin->getId(), 'administrator', $admin->getValueArray());
    }
}