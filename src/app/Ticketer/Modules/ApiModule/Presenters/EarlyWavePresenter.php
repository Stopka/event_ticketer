<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Notifiers\EarlyWaveInviteNotifier;
use Nette\Application\AbortException;
use Nette\Application\Responses\TextResponse;

class EarlyWavePresenter extends BasePresenter
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
     * @param string $id
     * @throws AbortException
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public function actionSend(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $this->earlyWaveInviteNotifier->sendDebugEarlyWaveInvites($uuid);
        $this->sendResponse(new TextResponse("OK"));
    }
}
