<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\IReserveApplicationFormWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Forms\ReserveApplicationFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\ApplicationsGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\IApplicationsGridWrapperFactory;
use Ticketer\Model\Exceptions\NotFoundException;
use Ticketer\Modules\AdminModule\Responses\ApplicationsExportResponse;
use Ticketer\Modules\FrontModule\Controls\IOccupancyControlFactory;
use Ticketer\Modules\FrontModule\Controls\OccupancyControl;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Modules\FrontModule\Responses\ApplicationPdfResponse;
use Ticketer\Responses\PdfResponse\PdfResponse;

class ApplicationPresenter extends BasePresenter
{

    public IApplicationsGridWrapperFactory $applicationsGridWrapperFactory;

    public IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory;

    public EventDao $eventDao;

    public ApplicationDao $applicationDao;

    public IOccupancyControlFactory $occupancyControlFactory;

    public ApplicationPdfResponse $applicationPdfResponse;

    /**
     * ApplicationPresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param IApplicationsGridWrapperFactory $applicationsGridWrapperFactory
     * @param IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory
     * @param EventDao $eventDao
     * @param ApplicationDao $applicationDao
     * @param IOccupancyControlFactory $occupancyControlFactory
     * @param ApplicationPdfResponse $applicationPdfResponse
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        IApplicationsGridWrapperFactory $applicationsGridWrapperFactory,
        IReserveApplicationFormWrapperFactory $reserveApplicationFormWrapperFactory,
        EventDao $eventDao,
        ApplicationDao $applicationDao,
        IOccupancyControlFactory $occupancyControlFactory,
        ApplicationPdfResponse $applicationPdfResponse
    ) {
        parent::__construct($dependencies);
        $this->applicationsGridWrapperFactory = $applicationsGridWrapperFactory;
        $this->reserveApplicationFormWrapperFactory = $reserveApplicationFormWrapperFactory;
        $this->eventDao = $eventDao;
        $this->applicationDao = $applicationDao;
        $this->occupancyControlFactory = $occupancyControlFactory;
        $this->applicationPdfResponse = $applicationPdfResponse;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionDefault(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var ApplicationsGridWrapper $applicationGrid */
        $applicationGrid = $this->getComponent('applicationsGrid');
        $applicationGrid->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionOccupancy(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
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
     * @param string $id
     * @throws AbortException
     */
    public function actionReserve(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var ReserveApplicationFormWrapper $reserveForm */
        $reserveForm = $this->getComponent('reserveForm');
        $reserveForm->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @param string[] $ids
     * @throws AbortException
     */
    public function actionEditReservation(string $id, array $ids): void
    {
        $uuid = Uuid::fromString($id);
        $eventEntity = $this->eventDao->getEvent($uuid);
        if (null === $eventEntity) {
            $this->redirect('Homepage:');
        }
        $reservedApplications = $this->applicationDao->getReservedApplications($eventEntity, $ids);
        $count = count($reservedApplications);
        if (count($reservedApplications) > 0) {
            $this->redirect('Homepage:');
        }
        $this->template->count = $count;
        /** @var ReserveApplicationFormWrapper $reserveForm */
        $reserveForm = $this->getComponent('reserveForm');
        $reserveForm->setEvent($eventEntity);
        $reserveForm->setApplications($reservedApplications);
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function renderExport(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->redirect('Homepage:');
        }
        $response = new ApplicationsExportResponse(
            $event,
            $this->applicationDao->getAllEventApplications($event),
            $this->getTranslator()
        );
        $this->sendResponse($response);
    }

    /**
     * @param string $id application id
     * @throws AbortException
     */
    public function renderPdf(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $application = $this->applicationDao->getApplication($uuid);
        if (null === $application) {
            throw new NotFoundException();
            //TODO better handling
        }
        $response = $this->applicationPdfResponse;
        //$this->addComponent($response, 'response');
        $response->setApplication($application);
        $response->setSaveMode(PdfResponse::DOWNLOAD);
        $this->sendResponse($response);
    }

    protected function createComponentApplicationsGrid(): ApplicationsGridWrapper
    {
        return $this->applicationsGridWrapperFactory->create();
    }

    protected function createComponentReserveForm(): ReserveApplicationFormWrapper
    {
        return $this->reserveApplicationFormWrapperFactory->create();
    }

    protected function createComponentOccupancy(): OccupancyControl
    {
        return $this->occupancyControlFactory->create();
    }
}
