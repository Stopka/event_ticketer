<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Ticketer\Model\Database\Entities\EventEntity;

interface IDelegateReservationControlsBuilderFactory
{

    public function create(EventEntity $eventEntity): DelegateReservationControlsBuilder;
}
