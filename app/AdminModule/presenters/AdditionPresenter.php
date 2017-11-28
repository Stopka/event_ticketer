<?php

namespace App\AdminModule\Presenters;


use App\Model\Persistence\Dao\EventDao;

class AdditionPresenter extends BasePresenter {

    /** @var  EventDao */
    private $eventDao;

    public function __construct(EventDao $eventDao) {
        parent::__construct();
        $this->eventDao = $eventDao;
    }

    public function actionDefault($id = null) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->redirect("Event:edit");
        }
        $this->template->event = $event;
    }
}
