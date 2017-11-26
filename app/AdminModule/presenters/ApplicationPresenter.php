<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\IReserveApplicationFormWrapperFactory;
use App\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
use App\AdminModule\Controls\Grids\ApplicationsGridWrapper;
use App\AdminModule\Controls\Grids\IApplicationsGridWrapperFactory;
use App\AdminModule\Responses\ApplicationsExportResponse;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EventDao;

class ApplicationPresenter extends BasePresenter {

    /** @var  IApplicationsGridWrapperFactory */
    public $applicationsGridWrapperFactory;

    /** @var  IReserveApplicationFormWrapperFactory */
    public $reserveApplicationFormWrapperFactory;

    /** @var EventDao */
    public $eventDao;

    /** @var ApplicationDao */
    public $applicationDao;

    /**
     * ApplicationPresenter constructor.
     * @param IApplicationsGridWrapperFactory $applicationsGridWrapperFactory
     * @param IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory
     * @param EventDao $eventDao
     * @param ApplicationDao $applicationDao
     */
    public function __construct(IApplicationsGridWrapperFactory $applicationsGridWrapperFactory, IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory, EventDao $eventDao, ApplicationDao $applicationDao) {
        parent::__construct();
        $this->applicationsGridWrapperFactory = $applicationsGridWrapperFactory;
        $this->reserveApplicationFormWrapperFactory = $reserveApplicationFormWrapperFactory;
        $this->eventDao = $eventDao;
        $this->applicationDao = $applicationDao;
    }


    public function actionDefault($id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        /** @var ApplicationsGridWrapper $applicationGrid */
        $applicationGrid = $this->getComponent('applicationsGrid');
        $applicationGrid->setEvent($event);
        $this->template->event = $event;
    }

    public function actionReserve($id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        /** @var ReserveApplicationFormWrapper $reserveForm */
        $reserveForm = $this->getComponent('reserveForm');
        $reserveForm->setEvent($event);
        $this->template->event = $event;
    }

    public function renderExport($id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        $response = new ApplicationsExportResponse($event, $this->applicationDao->getAllEventApplications($event));
        $this->sendResponse($response);
    }

    protected function createComponentApplicationsGrid() {
        return $this->applicationsGridWrapperFactory->create();
    }

    protected function createComponentReserveForm() {
        return $this->reserveApplicationFormWrapperFactory->create();
    }
}
