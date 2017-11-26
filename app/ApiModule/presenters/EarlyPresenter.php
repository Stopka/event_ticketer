<?php

namespace App\ApiModule\Presenters;

use Nette\Application\Responses\TextResponse;
use Tracy\Debugger;


class EarlyPresenter extends BasePresenter {

    /**
     * @var \App\Model\Persistence\Dao\EarlyWaveDao
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
