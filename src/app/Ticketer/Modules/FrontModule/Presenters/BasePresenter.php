<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Ticketer\Controls\Menus\Menu;
use Ticketer\Modules\FrontModule\Controls\Menus\MenuFactory;

/**
 * Base presenter for front application presenters.
 */
abstract class BasePresenter extends \Ticketer\Presenters\BasePresenter
{
    private MenuFactory $menuFactory;

    /** @persistent null|string Určuje jazykovou verzi webu. */
    public ?string $locale;

    public function __construct(BasePresenterDependencies $dependencies)
    {
        parent::__construct($dependencies->getParentDependencies());
        $this->menuFactory = $dependencies->getMenuFactory();
    }

    protected function createComponentMenu(): Menu
    {
        return $this->menuFactory->create();
    }

    protected function getMenu(): Menu
    {
        return $this->getComponent('menu');
    }
}
