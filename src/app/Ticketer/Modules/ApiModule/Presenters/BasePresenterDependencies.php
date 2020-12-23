<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Ticketer\Presenters\BasePresenterDependencies as ParentPresenterDependencies;

class BasePresenterDependencies
{
    private ParentPresenterDependencies $parentDependencies;

    public function __construct(ParentPresenterDependencies $dependencies)
    {
        $this->parentDependencies = $dependencies;
    }

    /**
     * @return ParentPresenterDependencies
     */
    public function getParentDependencies(): ParentPresenterDependencies
    {
        return $this->parentDependencies;
    }
}
