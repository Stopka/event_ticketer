<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Ticketer\Modules\ApiModule\Http\ApiHttpAuthenticator;
use Ticketer\Presenters\BasePresenter as UpperBasePresenter;

/**
 * Base presenter for admin application presenters.
 */
abstract class BasePresenter extends UpperBasePresenter
{
    private ApiHttpAuthenticator $httpAuthenticator;

    public function __construct(BasePresenterDependencies $dependencies)
    {
        parent::__construct($dependencies->getParentDependencies());
        $this->httpAuthenticator = $dependencies->getHttpAuthenticator();
    }

    protected function assertAuthentication(): void
    {
        $this->httpAuthenticator->authenticate($this->getHttpRequest(), $this->getHttpResponse());
    }

    public function startup(): void
    {
        $this->assertAuthentication();
        parent::startup();
    }


}
