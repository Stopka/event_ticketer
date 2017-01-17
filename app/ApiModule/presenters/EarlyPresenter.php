<?php

namespace App\ApiModule\Presenters;

use App\Model;
use Nette\Application\Responses\TextResponse;


class EarlyPresenter extends BasePresenter {

    /**
     * @var Model\Facades\EarlyWaveFacade
     * @inject
     */
    public $earlyWaveFacede;

    public function renderSendEmails() {
        $this->earlyWaveFacede->sendEmails(1);
        $this->sendResponse(new TextResponse('OK'));
    }

}
