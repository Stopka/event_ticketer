<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Http;

use Nette\Http\IRequest;
use Nette\Http\IResponse;

class CompositeAuthenticator implements HttpAuthenticatorInterface
{
    /** @var HttpAuthenticatorInterface[] */
    private array $authenticators;

    /**
     * CompositeAuthenticator constructor.
     * @param HttpAuthenticatorInterface[] $authenticators
     */
    public function __construct(array $authenticators)
    {
        $this->authenticators = $authenticators;
    }

    public function authenticate(IRequest $request, IResponse $response): void
    {
        foreach ($this->authenticators as $authenticator) {
            if (!$authenticator->hasCredentials()) {
                continue;
            }
            $authenticator->authenticate($request, $response);

            return;
        }
        $response->setCode(IResponse::S401_UNAUTHORIZED);

        echo '<h1>Authentication failed.</h1>';
        echo '<p>No api authentication is configured</p>';
        die;
    }

    public function hasCredentials(): bool
    {
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->hasCredentials()) {
                return true;
            }
        }

        return false;
    }
}
