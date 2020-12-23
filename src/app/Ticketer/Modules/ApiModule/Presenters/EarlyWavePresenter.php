<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Nette\Application\UI\InvalidLinkException;
use Ticketer\Model\Notifiers\EarlyWaveInviteNotifier;
use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;

class EarlyWavePresenter extends DebugPresenter
{

    private EarlyWaveInviteNotifier $earlyWaveInviteNotifier;

    public function __construct(
        BasePresenterDependencies $dependencies,
        EarlyWaveInviteNotifier $earlyWaveInviteNotifier
    ) {
        parent::__construct($dependencies);
        $this->earlyWaveInviteNotifier = $earlyWaveInviteNotifier;
    }


    /**
     * @param int $id
     * @throws InvalidLinkException
     * @throws AbortException
     */
    public function actionSend(int $id): void
    {
        $this->earlyWaveInviteNotifier->sendDebugEarlyWaveInvites($id);
        $this->sendResponse(new TextResponse("OK"));
    }
}
