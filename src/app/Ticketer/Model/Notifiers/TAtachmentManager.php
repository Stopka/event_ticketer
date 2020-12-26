<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Ticketer\Model\Database\Entities\EventEntity;

trait TAtachmentManager
{
    /** @var IEventMessageAtachmentManagerFactory */
    private $atachmentManagerFactory;

    /** @var array<string,EventMessageAtachmentManager> */
    private $atachmentManager = [];


    /**
     * @param IEventMessageAtachmentManagerFactory $atachmentManagerFactory
     */
    protected function injectAtachmentManagerFactory(
        IEventMessageAtachmentManagerFactory $atachmentManagerFactory
    ): void {
        $this->atachmentManagerFactory = $atachmentManagerFactory;
    }

    abstract protected function getAtachmentManagerNamespace(): string;

    /**
     * @param EventEntity $eventEntity
     * @return EventMessageAtachmentManager
     */
    public function getAtachmentManager(EventEntity $eventEntity): EventMessageAtachmentManager
    {
        $eventId = $eventEntity->getId()->toString();
        if (!isset($this->atachmentManager[$eventId])) {
            $this->atachmentManager[$eventId] = $this->atachmentManagerFactory->create(
                $this->getAtachmentManagerNamespace(),
                $eventEntity
            );
        }

        return $this->atachmentManager[$eventId];
    }
}
