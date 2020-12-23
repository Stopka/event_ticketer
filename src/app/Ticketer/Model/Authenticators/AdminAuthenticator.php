<?php

declare(strict_types=1);

namespace Ticketer\Model\Authenticators;

use Ticketer\Model\Database\Daos\AdministratorDao;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\SmartObject;

class AdminAuthenticator implements IAuthenticator
{
    use SmartObject;

    /** @var  AdministratorDao */
    private $administratorDao;

    public function __construct(AdministratorDao $administratorDao)
    {
        $this->administratorDao = $administratorDao;
    }


    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @param mixed[] $credentials
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials): IIdentity
    {
        [$username, $password] = $credentials;
        $admin = $this->administratorDao->getAdministratorByUsername($username);
        if (null === $admin) {
            throw new AuthenticationException('Admin does not exist');
        }
        if (!$admin->verifyPassword($password)) {
            throw new AuthenticationException('Wrong password');
        }

        return new Identity($admin->getId(), 'administrator', $admin->getValueArray());
    }
}
