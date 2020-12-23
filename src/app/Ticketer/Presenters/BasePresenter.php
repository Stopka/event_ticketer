<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Ticketer\Controls\TFlashTranslatedMessage;
use Ticketer\Controls\TInjectTranslator;
use Ticketer\Model\Database\Daos\AdministratorDao;
use Ticketer\Model\Database\Entities\AdministratorEntity;
use Nette;

/**
 * Base presenter for all application presenters.
 * @method BaseTemplate getTemplate()
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use TInjectTranslator;
    use TFlashTranslatedMessage;

    /** @var string|null */
    public ?string $locale;

    private AdministratorDao $administratorDao;

    protected ?AdministratorEntity $administratorEntity;

    public function __construct(BasePresenterDependencies $dependencies)
    {
        parent::__construct();
        $this->administratorDao = $dependencies->getAdministratorDao();
        $this->injectTranslator($dependencies->getTransaltor());
    }

    /**
     * @throws Nette\Application\AbortException
     */
    public function startup(): void
    {
        parent::startup();
        if ($this->getUser()->isLoggedIn()) {
            $administratorEntity = $this->administratorDao->getAdministrator($this->getUser()->getId());
            if (null === $administratorEntity) {
                $this->getUser()->logout(true);
                $this->redirect('this');
            }
            $this->administratorEntity = $administratorEntity;
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->getTemplate()->administratorEntity = $this->administratorEntity;
    }
}
