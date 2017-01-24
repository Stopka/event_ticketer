<?php

namespace App\FrontModule\Presenters;

use App\Model;
use Tracy\Debugger;


class HomepagePresenter extends BasePresenter {

    /**
     * @var Model\Facades\EventFacade
     * @inject
     */
    public $eventFacade;

    /**
     * @var Model\Facades\ApplicationFacade
     * @inject
     */
    public $applicationFacade;

    public function renderDefault() {
        $events = $this->eventFacade->getPublicAvailibleEvents();
        $future_events = $this->eventFacade->getPublicFutureEvents();
        Debugger::barDump($future_events);
        if(count($events)==1 && !$future_events){
            $this->redirect('Event:',$events[0]->getId());
        }
        $this->template->events = $events;
        $this->template->future_events = $future_events;
    }

    /**
     * @param $event
     * @return integer
     */
    public function countApplications(Model\Entities\EventEntity $event){
        return $this->applicationFacade->countIssuedApplications($event);
    }

}
