<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Contributte\Console\Application;
use Nette\Application\AbortException;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Ticketer\Modules\ApiModule\Responses\ConsoleOutputResponse;

class MigrationsPresenter extends BasePresenter
{
    private AnsiToHtmlConverter $colorConverter;
    private Application $application;

    public function __construct(BasePresenterDependencies $dependencies, Application $application)
    {
        parent::__construct($dependencies);
        $this->application = $application;
    }

    /**
     * @param string $version
     * @throws AbortException
     */
    public function actionMigrate(string $version = 'latest'): void
    {
        $this->application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'migrations:migrate',
                'version' => $version,
                '--no-interaction' => true,
                '--all-or-nothing' => true,
                '--ansi' => true
            ]
        );
        $output = new BufferedOutput();
        $result = $this->application->run($input, $output);

        $this->sendResponse(
            new ConsoleOutputResponse($result, $output)
        );
    }
}
