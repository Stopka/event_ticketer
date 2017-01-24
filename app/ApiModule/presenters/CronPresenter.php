<?php

namespace App\ApiModule\Presenters;

use App\Model;
use Nette\Application\Responses\TextResponse;


class CronPresenter extends BasePresenter {

    /**
     * @var Model\Facades\EarlyWaveFacade
     * @inject
     */
    public $earlyWaveFacede;

    public function actionDefault() {
        $this->earlyWaveFacede->sendUnsentInvites();
        $this->sendResponse(new TextResponse('OK'));
    }

}
