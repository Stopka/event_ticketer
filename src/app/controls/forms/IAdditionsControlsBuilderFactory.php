<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 22:14
 */

namespace App\Controls\Forms;

use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EventEntity;

interface IAdditionsControlsBuilderFactory {
    public function create(
        EventEntity $eventEntity,
        CurrencyEntity $currencyEntity
    ): AdditionsControlsBuilder;
}