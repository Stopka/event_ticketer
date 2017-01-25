<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\IReserveApplicationFormWrapperFactory;
use App\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
use App\AdminModule\Controls\Grids\ApplicationsGridWrapper;
use App\AdminModule\Controls\Grids\IApplicationsGridWrapperFactory;
use App\AdminModule\Responses\ApplicationsExportResponse;
use App\Model\Facades\ApplicationFacade;
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

    /**
     * @var ApplicationFacade
     * @inject
     */
    public $applicationFacade;

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

    public function renderExport($id) {
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
            $this->redirect('Homepage:');
        }
        $response = new ApplicationsExportResponse($event,$this->applicationFacade->getAllEventApplications($event));
        $this->sendResponse($response);
    }

    protected function createComponentApplicationsGrid(){
        return $this->applicationsGridWrapperFactory->create();
    }

    protected function createComponentReserveForm(){
        return $this->reserveApplicationFormWrapperFactory->create();
    }
}
