<?php

declare(strict_types=1);

namespace Ticketer\Templates;

use Nette\Bridges\ApplicationLatte\Template;
use Ticketer\Model\Database\Entities\AdministratorEntity;
use Ticketer\Templates\NetteTemplateTrait;

class BaseTemplate extends Template
{
    use NetteTemplateTrait;

    public ?AdministratorEntity $administratorEntity;
}
