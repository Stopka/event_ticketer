<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Templates;

use Ticketer\Model\Database\Entities\EventEntity;

class SubstituteTemplate extends BaseTemplate
{
    public EventEntity $event;
}