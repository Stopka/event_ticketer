<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Ticketer\Model\Database\Entities\SubstituteEntity;

class SubstituteCreatedEvent extends Event
{
    private SubstituteEntity $substitute;

    public function __construct(SubstituteEntity $substitute)
    {
        $this->substitute = $substitute;
    }

    /**
     * @return SubstituteEntity
     */
    public function getSubstitute(): SubstituteEntity
    {
        return $this->substitute;
    }
}
