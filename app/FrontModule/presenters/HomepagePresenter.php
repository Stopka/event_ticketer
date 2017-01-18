<?php

namespace App\FrontModule\Presenters;

use App\Model;


class HomepagePresenter extends BasePresenter {

    /**
     * @var Model\Facades\EventFacade
     * @inject
     */
    public $eventFacade;

    public function renderDefault() {
        $events = $this->eventFacade->getPublicAvailibleEvents();
        if(count($events)==1){
            $this->redirect('Event:',$events[0]->getId());
        }
        $this->template->events = $events;
    }

}
