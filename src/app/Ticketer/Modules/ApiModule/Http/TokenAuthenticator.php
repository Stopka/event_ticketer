<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Http;

use Nette\Http\IRequest;
use Nette\Http\IResponse;

class TokenAuthenticator implements HttpAuthenticatorInterface
{
    public const QUERY_KEY = 'auth_token';

    /** @var array<string> */
    private array $tokens;

    /**
     * TokenAuthenticator constructor.
     * @param array<string> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function authenticate(IRequest $request, IResponse $response): void
    {
        $token = (string)$request->getQuery(self::QUERY_KEY);
        if ('' !== $token && in_array($token, $this->tokens, true)) {
            return;
        }
        $response->setCode(IResponse::S401_UNAUTHORIZED);

        echo '<h1>Authentication failed.</h1>';
        echo '<p>Invalid token.</p>';
        die;
    }

    public function hasCredentials(): bool
    {
        return count($this->tokens) > 0;
    }
}
