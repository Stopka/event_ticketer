<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\EventFormWrapper;
use App\AdminModule\Controls\Forms\IEventFromWrapperFactory;
use App\AdminModule\Controls\Grids\EventsGridWrapper;
use App\AdminModule\Controls\Grids\IEventsGridWrapperFactory;
use App\Model\Persistence\Dao\EventDao;

class EventPresenter extends BasePresenter {

    /** @var  IEventsGridWrapperFactory */
    private $eventsGridWrapperFactory;

    /** @var  IEventFromWrapperFactory */
    private $eventFormWrapperFactory;

    /** @var  EventDao */
    private $eventDao;

    public function __construct(IEventsGridWrapperFactory $eventsGridWrapperFactory, EventDao $eventDao, IEventFromWrapperFactory $eventFromWrapperFactory) {
        parent::__construct();
        $this->eventsGridWrapperFactory = $eventsGridWrapperFactory;
        $this->eventDao = $eventDao;
        $this->eventFormWrapperFactory = $eventFromWrapperFactory;
    }

    public function actionDefault() {

    }

    public function actionAdd() {
        $this->redirect("edit");
    }

    public function actionEdit($id = null) {
        $event = $this->eventDao->getEvent($id);
        if (!$event && $id) {
            $this->redirect('edit');
        }
        /** @var EventFormWrapper $eventForm */
        $eventForm = $this->getComponent('eventForm');
        $eventForm->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @return EventsGridWrapper
     */
    protected function createComponentEventsGrid() {
        return $this->eventsGridWrapperFactory->create();
    }

    /**
     * @return EventFormWrapper
     */
    protected function createComponentEventForm() {
        return $this->eventFormWrapperFactory->create();
    }

}
