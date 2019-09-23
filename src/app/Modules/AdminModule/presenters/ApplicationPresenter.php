<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\IReserveApplicationFormWrapperFactory;
use App\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
use App\AdminModule\Controls\Grids\ApplicationsGridWrapper;
use App\AdminModule\Controls\Grids\IApplicationsGridWrapperFactory;
use App\AdminModule\Responses\ApplicationsExportResponse;
use App\FrontModule\Controls\IOccupancyControlFactory;
use App\FrontModule\Controls\OccupancyControl;
use App\FrontModule\Responses\ApplicationPdfResponse;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;
use Joseki\Application\Responses\PdfResponse;

class ApplicationPresenter extends BasePresenter {

    /** @var  IApplicationsGridWrapperFactory */
    public $applicationsGridWrapperFactory;

    /** @var  IReserveApplicationFormWrapperFactory */
    public $reserveApplicationFormWrapperFactory;

    /** @var EventDao */
    public $eventDao;

    /** @var ApplicationDao */
    public $applicationDao;

    /** @var  IOccupancyControlFactory */
    public $occupancyControlFactory;

    /**
     * @var ApplicationPdfResponse
     * @inject
     */
    public $applicationPdfResponse;

    /**
     * ApplicationPresenter constructor.
     * @param IApplicationsGridWrapperFactory $applicationsGridWrapperFactory
     * @param IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory
     * @param EventDao $eventDao
     * @param ApplicationDao $applicationDao
     * @param IOccupancyControlFactory $occupancyControlFactory
     */
    public function __construct(
        IApplicationsGridWrapperFactory $applicationsGridWrapperFactory,
        IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory,
        EventDao $eventDao,
        ApplicationDao $applicationDao,
        IOccupancyControlFactory $occupancyControlFactory,
        ApplicationPdfResponse $applicationPdfResponse
    ) {
        parent::__construct();
        $this->applicationsGridWrapperFactory = $applicationsGridWrapperFactory;
        $this->reserveApplicationFormWrapperFactory = $reserveApplicationFormWrapperFactory;
        $this->eventDao = $eventDao;
        $this->applicationDao = $applicationDao;
        $this->occupancyControlFactory = $occupancyControlFactory;
        $this->applicationPdfResponse = $applicationPdfResponse;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(int $id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var ApplicationsGridWrapper $applicationGrid */
        $applicationGrid = $this->getComponent('applicationsGrid');
        $applicationGrid->setEvent($event);
        $this->template->event = $event;

    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionOccupancy(int $id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var OccupancyControl $occupancy */
        $occupancy = $this->getComponent('occupancy');
        $occupancy->setEvent($event);
        $occupancy->setAdmin();
        $this->template->event = $event;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionReserve(int $id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var ReserveApplicationFormWrapper $reserveForm */
        $reserveForm = $this->getComponent('reserveForm');
        $reserveForm->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @param int[] $ids
     * @throws \Nette\Application\AbortException
     */
    public function actionEditReservation(int $id, array $ids) {
        $eventEntity = $this->eventDao->getEvent($id);
        if (!$eventEntity) {
            $this->redirect('Homepage:');
        }
        $reservedApplications = $this->applicationDao->getReservedApplications($eventEntity, $ids);
        $count = count($reservedApplications);
        if (!count($reservedApplications)) {
            $this->redirect('Homepage:');
        }
        $this->template->count = $count;
        /** @var ReserveApplicationFormWrapper $reserveForm */
        $reserveForm = $this->getComponent('reserveForm');
        $reserveForm->setEvent($eventEntity);
        $reserveForm->setApplications($reservedApplications);
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function renderExport(int $id) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->redirect('Homepage:');
        }
        $response = new ApplicationsExportResponse($event, $this->applicationDao->getAllEventApplications($event), $this->getTranslator());
        $this->sendResponse($response);
    }

    protected function createComponentApplicationsGrid() {
        return $this->applicationsGridWrapperFactory->create();
    }

    protected function createComponentReserveForm() {
        return $this->reserveApplicationFormWrapperFactory->create();
    }

    protected function createComponentOccupancy() {
        return $this->occupancyControlFactory->create();
    }

    /**
     * @param int $applicationId
     * @throws \Nette\Application\AbortException
     */
    public function renderPdf(int $id) {
        $application = $this->applicationDao->getApplication($id);
        $response = $this->applicationPdfResponse;
        //$this->addComponent($response, 'response');
        $response->setApplication($application);
        $response->setSaveMode(PdfResponse::DOWNLOAD);
        $this->sendResponse($response);
    }
}
