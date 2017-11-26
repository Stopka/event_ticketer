<?php

namespace App\ApiModule\Presenters;

use App\Model\Notifier\EarlyWaveInviteNotifier;
use Nette\Application\Responses\TextResponse;
use Tracy\Debugger;


class EarlyPresenter extends BasePresenter {

    /** @var EarlyWaveInviteNotifier */
    public $earlyWaveInviteNotifier;

    /**
     * EarlyPresenter constructor.
     * @param EarlyWaveInviteNotifier $earlyWaveInviteNotifier
     */
    public function __construct(EarlyWaveInviteNotifier $earlyWaveInviteNotifier) {
        parent::__construct();
        $this->earlyWaveInviteNotifier = $earlyWaveInviteNotifier;

    }


    public function renderSendEmails() {
        if(!Debugger::$productionMode){
            $this->earlyWaveInviteNotifier->sendUnsentInvites();
        }
        $this->sendResponse(new TextResponse('OK'));
    }

}
