<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Ticketer\Model\Database\Entities\EarlyEntity;

class EarlyAddedToWaveEvent extends Event
{
    private EarlyEntity $early;

    public function __construct(EarlyEntity $early)
    {
        $this->early = $early;
    }

    /**
     * @return EarlyEntity
     */
    public function getEarly(): EarlyEntity
    {
        return $this->early;
    }
}
