<?php

declare(strict_types=1);


namespace Ticketer\Modules\ApiModule\Http;


use Contributte\Http\Auth\BasicAuthenticator;

class ApiHttpAuthenticator extends BasicAuthenticator
{
    private const TITLE = 'Ticketer API';

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

}
