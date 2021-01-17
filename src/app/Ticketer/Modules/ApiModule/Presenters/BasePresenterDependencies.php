<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Ticketer\Modules\ApiModule\Http\BasicHttpAuthenticator;
use Ticketer\Modules\ApiModule\Http\HttpAuthenticatorInterface;
use Ticketer\Presenters\BasePresenterDependencies as ParentPresenterDependencies;

class BasePresenterDependencies
{
    private ParentPresenterDependencies $parentDependencies;

    private HttpAuthenticatorInterface $httpAuthenticator;

    public function __construct(
        ParentPresenterDependencies $dependencies,
        HttpAuthenticatorInterface $httpAuthenticator
    ) {
        $this->parentDependencies = $dependencies;
        $this->httpAuthenticator = $httpAuthenticator;
    }

    /**
     * @return ParentPresenterDependencies
     */
    public function getParentDependencies(): ParentPresenterDependencies
    {
        return $this->parentDependencies;
    }

    /**
     * @return HttpAuthenticatorInterface
     */
    public function getHttpAuthenticator(): HttpAuthenticatorInterface
    {
        return $this->httpAuthenticator;
    }
}
