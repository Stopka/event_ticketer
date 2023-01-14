<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Modules\AdminModule\Controls\Menus\MenuFactory;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Menus\Menu;

/**
 * Base presenter for admin application presenters.
 */
abstract class BasePresenter extends \Ticketer\Presenters\BasePresenter
{
    private MenuFactory $menuFactory;

    public function __construct(BasePresenterDependencies $dependencies)
    {
        parent::__construct($dependencies->getParentDependencies());
        $this->menuFactory = $dependencies->getMenuFactory();
    }


    public function startup(): void
    {
        parent::startup();
        $this->assertLoggedUser();
    }

    /**
     * @throws AbortException
     */
    protected function assertLoggedUser(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            return;
        }
        $this->flashTranslatedMessage('Error.Permission.NotSignedIn', FlashMessageTypeEnum::WARNING());
        $backlink = $this->storeRequest();
        $this->redirect('Sign:in', ['backlink' => $backlink]);
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
