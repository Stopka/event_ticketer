<?php

declare(strict_types=1);

namespace Ticketer\Model\Authenticators;

use Nette\Security\Authenticator;
use Nette\Security\SimpleIdentity;
use Ticketer\Model\Database\Daos\AdministratorDao;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\SmartObject;

class AdminAuthenticator implements Authenticator
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
     * @param string $user
     * @param string $password
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(string $user, string $password): IIdentity
    {
        $admin = $this->administratorDao->getAdministratorByUsername($user);
        if (null === $admin) {
            throw new AuthenticationException('Admin does not exist');
        }
        if (!$admin->verifyPassword($password)) {
            throw new AuthenticationException('Wrong password');
        }

        return new SimpleIdentity($admin->getId()->toString(), 'administrator', $admin->getValueArray());
    }
}
