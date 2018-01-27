<?php

namespace App\ApiModule\Presenters;

use App\Model\Notifier\EarlyWaveInviteNotifier;
use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;

class EarlyWavePresenter extends DebugPresenter {

    /**
     * @var EarlyWaveInviteNotifier
     * @inject
     */
    public $earlyWaveInviteNotifier;

    /**
     * @param int $id
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws AbortException
     */
    public function actionSend(int $id) {
        $this->earlyWaveInviteNotifier->sendDebugEarlyWaveInvites($id);
        $this->sendResponse(new TextResponse("OK"));
    }
}
