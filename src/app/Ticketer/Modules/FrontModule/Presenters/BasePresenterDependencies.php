<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Ticketer\Modules\FrontModule\Controls\Menus\MenuFactory;
use Ticketer\Presenters\BasePresenterDependencies as ParentPresenterDependencies;

class BasePresenterDependencies
{
    private MenuFactory $menuFactory;

    private ParentPresenterDependencies $parentDependencies;

    public function __construct(ParentPresenterDependencies $dependencies, MenuFactory $menuFactory)
    {
        $this->menuFactory = $menuFactory;
        $this->parentDependencies = $dependencies;
    }

    /**
     * @return ParentPresenterDependencies
     */
    public function getParentDependencies(): ParentPresenterDependencies
    {
        return $this->parentDependencies;
    }


    /**
     * @return MenuFactory
     */
    public function getMenuFactory(): MenuFactory
    {
        return $this->menuFactory;
    }
}
