<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Nette\Security\User;

trait NetteTemplateTrait
{
    public string $baseUrl;
    public string $basePath;
    public User $user;
    /** @var array<FlashMessage> */
    public array $flashes;
}
