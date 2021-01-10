<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Ticketer\Modules\ApiModule\Http\ApiHttpAuthenticator;
use Ticketer\Presenters\BasePresenterDependencies as ParentPresenterDependencies;

class BasePresenterDependencies
{
    private ParentPresenterDependencies $parentDependencies;

    private ApiHttpAuthenticator $httpAuthenticator;

    public function __construct(ParentPresenterDependencies $dependencies, ApiHttpAuthenticator $httpAuthenticator)
    {
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
     * @return ApiHttpAuthenticator
     */
    public function getHttpAuthenticator(): ApiHttpAuthenticator
    {
        return $this->httpAuthenticator;
    }
}
