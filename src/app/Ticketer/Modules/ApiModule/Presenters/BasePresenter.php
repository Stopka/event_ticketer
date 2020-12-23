<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Ticketer\Presenters\BasePresenter as UpperBasePresenter;

/**
 * Base presenter for admin application presenters.
 */
abstract class BasePresenter extends UpperBasePresenter
{
    public function __construct(BasePresenterDependencies $dependencies)
    {
        parent::__construct($dependencies->getParentDependencies());
    }
}
