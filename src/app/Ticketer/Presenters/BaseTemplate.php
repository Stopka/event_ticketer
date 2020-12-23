<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Nette\Bridges\ApplicationLatte\Template;
use Ticketer\Model\Database\Entities\AdministratorEntity;

class BaseTemplate extends Template
{
    use NetteTemplateTrait;

    public ?AdministratorEntity $administratorEntity;
}
