<?php

namespace App\ApiModule\Presenters;


use App\FrontModule\Responses\ApplicationPdfResponse;
use App\Model\Persistence\Dao\ApplicationDao;

class ApplicationPresenter extends DebugPresenter {

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
        $application = $this->appicationDao->getApplication($id);
        $response = $this->applicationPdfResponse;
        //$this->addComponent($response, 'response');
        $response->setApplication($application);
        $this->sendResponse($response);
    }
}
