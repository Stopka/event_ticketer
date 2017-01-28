<?php

namespace App\ApiModule\Presenters;

use App\Model;
use Nette\Application\Responses\TextResponse;
use Tracy\Debugger;


class EarlyPresenter extends BasePresenter {

    /**
     * @var Model\Facades\EarlyWaveFacade
     * @inject
     */
    public $earlyWaveFacede;

    public function renderSendEmails() {
        if(!Debugger::$productionMode){
            $this->earlyWaveFacede->sendEmails(1);
        }
        $this->sendResponse(new TextResponse('OK'));
    }

}
