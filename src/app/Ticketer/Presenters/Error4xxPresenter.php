<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Request;

class Error4xxPresenter extends BasePresenter
{
    /**
     * @throws BadRequestException
     */
    public function startup(): void
    {
        parent::startup();
        $request = $this->getRequest();
        if (null === $request || !$request->isMethod(Request::FORWARD)) {
            $this->error();
        }
    }


    public function renderDefault(BadRequestException $exception): void
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $this->getTemplate()->setFile(
            is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte'
        );
    }
}
