<?php

namespace App\FrontModule\Presenters;

use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;


class HomepagePresenter extends BasePresenter {

    /** @var EventDao */
    public $eventDao;

    /** @var ApplicationDao */
    public $applicationDao;

    /**
     * HomepagePresenter constructor.
     * @param EventDao $eventDao
     * @param ApplicationDao $applicationDao
     */
    public function __construct(EventDao $eventDao, ApplicationDao $applicationDao) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->applicationDao = $applicationDao;
    }


    public function renderDefault() {
        $events = $this->eventDao->getPublicAvailibleEvents();
        $future_events = $this->eventDao->getPublicFutureEvents();
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
    public function countApplications(EventEntity $event){
        return $this->applicationDao->countIssuedApplications($event);
    }

}
