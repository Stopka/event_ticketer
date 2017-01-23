<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\ApplicationsGridWrapper;
use App\AdminModule\Controls\Grids\IApplicationsGridWrapperFactory;
use App\Model\Facades\EventFacade;

class ApplicationPresenter extends BasePresenter {

    /**
     * @var  IApplicationsGridWrapperFactory
     * @inject
     */
    public $applicationsGridWrapperFactory;

    /**
     * @var EventFacade
     * @inject
     */
    public $eventFacade;

    public function renderDefault($id) {
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
            $this->redirect('Homepage:');
        }
        /** @var ApplicationsGridWrapper $applicationGrid */
        $applicationGrid = $this->getComponent('applicationsGrid');
        $applicationGrid->setEvent($event);
        $this->template->event = $event;
    }

    protected function createComponentApplicationsGrid(){
        return $this->applicationsGridWrapperFactory->create();
    }
}
