<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Ticketer\Modules\AdminModule\Controls\Menus\MenuFactory;
use Ticketer\Presenters\BasePresenterDependencies as ParentPresenterDependencies;

class BasePresenterDependencies
{

    private ParentPresenterDependencies $parentDependencies;

    private MenuFactory $menuFactory;

    public function __construct(ParentPresenterDependencies $parentDependencies, MenuFactory $menuFactory)
    {
        $this->parentDependencies = $parentDependencies;
        $this->menuFactory = $menuFactory;
    }

    /**
     * @return ParentPresenterDependencies
     */
    public function getParentDependencies(): ParentPresenterDependencies
    {
        return $this->parentDependencies;
    }

    public function getMenuFactory(): MenuFactory
    {
        return $this->menuFactory;
    }
}
