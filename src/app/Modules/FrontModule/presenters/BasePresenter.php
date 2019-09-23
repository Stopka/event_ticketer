<?php

namespace App\FrontModule\Presenters;

use App\Controls\Menus\Menu;
use App\FrontModule\Controls\Menus\MenuFactory;


/**
 * Base presenter for front application presenters.
 */
abstract class BasePresenter extends \App\Presenters\BasePresenter {

    /** @var MenuFactory */
    private $menuFactory;

    /** @persistent null|string Určuje jazykovou verzi webu. */
    public $locale;


    public function injectMenuFactory(MenuFactory $menuFactory) {
        $this->menuFactory = $menuFactory;
    }

    /**
     * @return \App\Controls\Menus\Menu
     */
    protected function createComponentMenu() {
        return $this->menuFactory->create();
    }

    protected function getMenu(): Menu {
        return $this->getComponent('menu');
    }
}
