<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\IReserveApplicationFormWrapperFactory;
use App\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
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
     * @var  IReserveApplicationFormWrapperFactory
     * @inject
     */
    public $reserveApplicationFormWrapperFactory;

    /**
     * @var EventFacade
     * @inject
     */
    public $eventFacade;

    public function actionDefault($id) {
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
            $this->redirect('Homepage:');
        }
        /** @var ApplicationsGridWrapper $applicationGrid */
        $applicationGrid = $this->getComponent('applicationsGrid');
        $applicationGrid->setEvent($event);
        $this->template->event = $event;
    }

    public function actionReserve($id) {
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
            $this->redirect('Homepage:');
        }
        /** @var ReserveApplicationFormWrapper $reserveForm */
        $reserveForm = $this->getComponent('reserveForm');
        $reserveForm->setEvent($event);
        $this->template->event = $event;
    }

    protected function createComponentApplicationsGrid(){
        return $this->applicationsGridWrapperFactory->create();
    }

    protected function createComponentReserveForm(){
        return $this->reserveApplicationFormWrapperFactory->create();
    }
}
