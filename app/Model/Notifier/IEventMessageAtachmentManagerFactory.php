<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.18
 * Time: 23:50
 */

namespace App\Model\Notifier;

use App\Model\Persistence\Entity\EventEntity;

interface IEventMessageAtachmentManagerFactory {

    public function create(string $namespace, EventEntity $eventEntity): EventMessageAtachmentManager;

}