<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Responses\ApplicationPdfRenderer;
use App\Model;


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

    /** @var  ApplicationPdfRenderer @inject */
    public $renderer;

    public function renderTest(){
        $event = $this->eventFacade->getEvent(1);
        $apps = $this->applicationFacade->getAllEventApplications($event);
        $this->renderer->setApplication($apps[0]);
        $this->sendResponse($this->renderer->getResponse());
    }

}
