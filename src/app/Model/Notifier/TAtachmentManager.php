<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;


use App\Model\Persistence\Entity\EventEntity;

trait TAtachmentManager {
    /** @var IEventMessageAtachmentManagerFactory */
    private $atachmentManagerFactory;

    /** @var EventMessageAtachmentManager[] */
    private $atachmentManager = [];


    /**
     * @param EmailService $emailService
     */
    protected function injectAtachmentManagerFactory(IEventMessageAtachmentManagerFactory $atachmentManagerFactory): void {
        $this->atachmentManagerFactory = $atachmentManagerFactory;
    }

    abstract function getAtachmentManagerNamespace(): string;

    /**
     * @param EventEntity $eventEntity
     * @return EventMessageAtachmentManager
     */
    public function getAtachmentManager(EventEntity $eventEntity): EventMessageAtachmentManager {
        $eventId = $eventEntity->getId();
        if (!isset($this->atachmentManager[$eventId])) {
            $this->atachmentManager[$eventId] = $this->atachmentManagerFactory->create(
                $this->getAtachmentManagerNamespace(),
                $eventEntity
            );
        }
        return $this->atachmentManager[$eventId];
    }
}