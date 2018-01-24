<?php

namespace App\ApiModule\Presenters;

use App\Model\Exception\NotReadyException;
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


    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function renderSendEmails() {
        if (Debugger::$productionMode) {
            throw new NotReadyException("Not availible in production");
        }
        $this->sendResponse(new TextResponse('OK'));
    }

}
