<?php

namespace App\ApiModule\Presenters;

use App\Model\CronService;
use Nette\Application\Responses\TextResponse;


class CronPresenter extends BasePresenter {

    /** @var CronService */
    public $cronService;

    public function __construct(CronService $cronService) {
        parent::__construct();
        $this->cronService = $cronService;
    }


    public function actionDefault() {
        $this->cronService->run();
        $this->sendResponse(new TextResponse('OK'));
    }

}
