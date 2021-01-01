<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Nette;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses;
use Tracy\ILogger;

class ErrorPresenter implements Nette\Application\IPresenter
{
    use Nette\SmartObject;

    /** @var ILogger */
    private ILogger $logger;

    /**
     * ErrorPresenter constructor.
     * @param ILogger $logger
     */
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param Request $request
     * @return IResponse
     */
    public function run(Request $request): IResponse
    {
        $e = $request->getParameter('exception');

        if ($e instanceof Nette\Application\BadRequestException) {
            [$module, , $sep] = Nette\Application\Helpers::splitName($request->getPresenterName());

            return new Responses\ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
        }

        $this->logger->log($e, ILogger::EXCEPTION);

        return new Responses\CallbackResponse(
            function (): void {
                require __DIR__ . '/templates/Error/500.phtml';
            }
        );
    }
}