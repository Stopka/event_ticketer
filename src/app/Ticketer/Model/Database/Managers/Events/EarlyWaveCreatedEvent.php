<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;

class EarlyWaveCreatedEvent extends Event
{
    private EarlyWaveEntity $earlyWave;

    public function __construct(EarlyWaveEntity $earlyWave)
    {
        $this->earlyWave = $earlyWave;
    }

    /**
     * @return EarlyWaveEntity
     */
    public function getEarlyWave(): EarlyWaveEntity
    {
        return $this->earlyWave;
    }
}
