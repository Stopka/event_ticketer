<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Controls\Menus\MenuFactory;
use App\Controls\Menus\Menu;


/**
 * Base presenter for admin application presenters.
 */
abstract class BasePresenter extends \App\Presenters\BasePresenter {

    /** @var MenuFactory */
    private $menuFactory;

    /**
     * @throws \Nette\Application\AbortException
     */
    public function startup() {
        parent::startup();
        $this->checkUser();
    }

    public function injectMenuFactory(MenuFactory $menuFactory) {
        $this->menuFactory = $menuFactory;
    }

    /**
     * Provede kontrolu, zda je uživatel přihlášen
     * pokud není přesměruje ho na přihlášení
     * @throws \Nette\Application\AbortException
     */
    protected function checkUser() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashTranslatedMessage('Error.Permission.NotSignedIn', self::FLASH_MESSAGE_TYPE_WARNING);
            $backlink = $this->storeRequest();
            $this->redirect('Sign:in', array('backlink' => $backlink));
        }
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