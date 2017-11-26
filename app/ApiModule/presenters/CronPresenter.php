<?php

namespace App\ApiModule\Presenters;

use App\Model;
use Nette\Application\Responses\TextResponse;


class CronPresenter extends BasePresenter {

    /**
     * @var Model\CronService
     * @inject
     */
    public $cronService;

    public function actionDefault() {
        $this->cronService->run();
        $this->sendResponse(new TextResponse('OK'));
    }

}
