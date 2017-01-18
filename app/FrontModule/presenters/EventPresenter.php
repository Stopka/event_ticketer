<?php

namespace App\FrontModule\Presenters;

use App\Model;


class EventPresenter extends BasePresenter {

    /**
     * @var Model\Facades\EventFacade
     * @inject
     */
    public $eventFacade;

    public function actionDefault($id = null){
        $this->redirect('register',$id);
    }

    public function renderRegister($id = null) {
        $event = $this->eventFacade->getStartedEvent($id);
        if(!$event){
            $this->flashMessage('Událost nebyla nalezena','warning');
            $this->redirect('Homepage:');
        }
    }

}
