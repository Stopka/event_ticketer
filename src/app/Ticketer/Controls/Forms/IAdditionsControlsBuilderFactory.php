<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Entities\EventEntity;

interface IAdditionsControlsBuilderFactory
{
    public function create(
        EventEntity $eventEntity,
        CurrencyEntity $currencyEntity
    ): AdditionsControlsBuilder;
}
