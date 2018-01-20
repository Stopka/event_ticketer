<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Model\Persistence\Entity\EventEntity;

interface IDelegateReservationControlsBuilderFactory {

    public function create(EventEntity $eventEntity): DelegateReservationControlsBuilder;
}