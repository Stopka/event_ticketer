<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Model\Cron\CronService;
use Nette\Application\Responses\TextResponse;

class CronPresenter extends BasePresenter
{

    /** @var CronService */
    public CronService $cronService;

    public function __construct(BasePresenterDependencies $dependencies, CronService $cronService)
    {
        parent::__construct($dependencies);
        $this->cronService = $cronService;
    }

    /**
     * @throws AbortException
     */
    public function actionDefault(): void
    {
        $this->cronService->run();
        $this->sendResponse(new TextResponse('OK'));
    }
}
