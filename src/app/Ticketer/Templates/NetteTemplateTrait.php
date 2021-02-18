<?php

declare(strict_types=1);

namespace Ticketer\Templates;

use Nette\Security\User;
use Ticketer\Presenters\FlashMessage;

trait NetteTemplateTrait
{
    public string $baseUrl;
    public string $basePath;
    public User $user;
    /** @var array<FlashMessage> */
    public array $flashes;
}
