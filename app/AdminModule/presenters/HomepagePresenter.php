<?php

namespace App\AdminModule\Presenters;


use App\Model\Facades\EventFacade;

class HomepagePresenter extends BasePresenter {

    /**
     * @var EventFacade
     * @inject
     */
    public $eventFacade;

    public function renderDefault() {
        $events = $this->eventFacade->getAllEvents();
        if(count($events)==1){
            $this->redirect('Application:',$events[0]->getId());
        }
        $this->template->events = $events;
    }

}
