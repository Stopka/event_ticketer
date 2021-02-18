<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Templates;

use Ticketer\Model\Database\Entities\EventEntity;

class HomepageTemplate extends BaseTemplate
{
    /** @var EventEntity[] */
    public array $events;

    /** @var EventEntity[] */
    public array $futureEvents;
}
