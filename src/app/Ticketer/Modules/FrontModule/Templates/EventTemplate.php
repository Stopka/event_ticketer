<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Templates;

use Ticketer\Model\Database\Entities\EventEntity;

class EventTemplate extends BaseTemplate
{
    public EventEntity $event;
}
