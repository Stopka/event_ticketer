<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Ticketer\Model\Database\Entities\EventEntity;

interface IEventMessageAtachmentManagerFactory
{
    public function create(string $namespace, EventEntity $eventEntity): EventMessageAtachmentManager;
}
