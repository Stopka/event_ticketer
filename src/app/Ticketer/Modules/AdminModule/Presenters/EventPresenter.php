<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Modules\AdminModule\Controls\Forms\EventFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Forms\IEventFromWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Grids\EventsGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\IEventsGridWrapperFactory;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;

class EventPresenter extends BasePresenter
{

    private IEventsGridWrapperFactory $eventsGridWrapperFactory;

    private IEventFromWrapperFactory $eventFormWrapperFactory;

    private EventDao $eventDao;

    public function __construct(
        BasePresenterDependencies $dependencies,
        IEventsGridWrapperFactory $eventsGridWrapperFactory,
        EventDao $additionDao,
        IEventFromWrapperFactory $eventFromWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->eventsGridWrapperFactory = $eventsGridWrapperFactory;
        $this->eventDao = $additionDao;
        $this->eventFormWrapperFactory = $eventFromWrapperFactory;
    }

    public function actionDefault(): void
    {
    }

    /**
     * @throws AbortException
     */
    public function actionAdd(): void
    {
        $this->redirect("edit");
    }

    /**
     * @param int|null $id
     * @throws AbortException
     */
    public function actionEdit(?int $id = null): void
    {
        $event = $this->eventDao->getEvent($id);
        if (null === $event && null !== $id) {
            $this->redirect('edit');
        }
        if (null !== $event) {
            $this->getMenu()->setLinkParam(EventEntity::class, $event);
        }
        /** @var EventFormWrapper $eventForm */
        $eventForm = $this->getComponent('eventForm');
        $eventForm->setEvent($event);
        $this->template->event = $event;
    }

    protected function createComponentEventsGrid(): EventsGridWrapper
    {
        return $this->eventsGridWrapperFactory->create();
    }

    protected function createComponentEventForm(): EventFormWrapper
    {
        return $this->eventFormWrapperFactory->create();
    }
}
