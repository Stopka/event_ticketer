<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Templates;

use Nette\Bridges\ApplicationLatte\Template;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Templates\NetteTemplateTrait;

class OccupancyTemplate extends Template
{
    use NetteTemplateTrait;

    public ?EventEntity $event;
    public bool $showHeaders;
}
