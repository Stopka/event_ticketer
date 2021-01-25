<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Contributte\Console\Application;
use Nette\Application\AbortException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Ticketer\Modules\ApiModule\Responses\ConsoleOutputResponse;

class ResolveApplicationStatesPresenter extends BasePresenter
{
    private Application $application;

    public function __construct(BasePresenterDependencies $dependencies, Application $application)
    {
        parent::__construct($dependencies);
        $this->application = $application;
    }

    /**
     * @param string $eventId
     * @throws AbortException
     */
    public function actionDefault(string $eventId): void
    {
        $this->application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'debug:resolveApplicationStates',
                'eventId' => $eventId,
                '--no-interaction' => true,
                '--ansi' => true,
            ]
        );
        $output = new BufferedOutput();
        $result = $this->application->run($input, $output);

        $this->sendResponse(
            new ConsoleOutputResponse($result, $output)
        );
    }
}
