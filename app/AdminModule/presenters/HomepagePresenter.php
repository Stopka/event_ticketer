<?php

namespace App\AdminModule\Presenters;


use App\Model\Persistence\Dao\EventDao;

class HomepagePresenter extends BasePresenter {

    /** @var EventDao */
    public $eventDao;

    /**
     * HomepagePresenter constructor.
     * @param EventDao $eventDao
     */
    public function __construct(EventDao $eventDao) {
        parent::__construct();
        $this->eventDao = $eventDao;
    }


    public function renderDefault() {
        $events = $this->eventDao->getAllEvents();
        if(count($events)==1){
            $this->redirect('Application:',$events[0]->getId());
        }
        $this->template->events = $events;
    }

}
