<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Http;

use Nette\Http\IRequest;
use Nette\Http\IResponse;

interface HttpAuthenticatorInterface
{
    public function authenticate(IRequest $request, IResponse $response): void;

    public function hasCredentials(): bool;
}
