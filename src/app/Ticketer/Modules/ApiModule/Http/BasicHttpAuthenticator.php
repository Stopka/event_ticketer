<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Http;

use Contributte\Http\Auth\BasicAuthenticator;

class BasicHttpAuthenticator extends BasicAuthenticator implements HttpAuthenticatorInterface
{
    private const TITLE = 'Ticketer API';

    private bool $hasUsers = false;

    /**
     * ApiHttpAuthenticator constructor.
     * @param array<string,string> $users
     * @param string $title
     */
    public function __construct(array $users, string $title = self::TITLE)
    {
        parent::__construct($title);
        foreach ($users as $user => $password) {
            $this->addUser($user, password_hash($password, PASSWORD_DEFAULT), false);
        }
    }

    public function addUser(string $user, string $password, bool $unsecured): BasicAuthenticator
    {
        $this->hasUsers = true;

        return parent::addUser($user, $password, $unsecured);
    }

    public function hasCredentials(): bool
    {
        return $this->hasUsers;
    }
}
