<?php

namespace App\ApiModule\Presenters;


use App\FrontModule\Responses\ApplicationPdfResponse;
use App\Model\Exception\NotReadyException;
use App\Model\Persistence\Dao\ApplicationDao;
use Tracy\Debugger;

class ApplicationPresenter extends BasePresenter {

    /**
     * @var ApplicationPdfResponse
     * @inject
     */
    public $applicationPdfResponse;

    /**
     * @var ApplicationDao
     * @inject
     */
    public $appicationDao;

    /**
     * @param int $applicationId
     * @throws \Nette\Application\AbortException
     */
    public function renderPdf(int $id) {
        if (Debugger::$productionMode) {
            throw new NotReadyException("Not availible in production");
        }
        $application = $this->appicationDao->getApplication($id);
        $response = $this->applicationPdfResponse;
        $this->addComponent($response, 'response');
        $response->setApplication($application);
        $this->sendResponse($response);
    }
}
