<?php

namespace App\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /** @var string|null */
    public $locale;

    /**
     * @var \Kdyby\Translation\Translator Obstarává jazykový překlad na úrovni presenteru.
     * @inject
     */
    public $translator;

    /**
     * @var \App\Model\Persistence\Dao\AdministratorDao Fasáda pro manipulaci s uživateli.
     * @inject
     */
    public $administratorFacade;

    /** @var \App\Model\Persistence\Entity\AdministratorEntity Entita pro aktuálního uživatele. */
    protected $administratorEntity;

    /**
     * Registrace makra na překlad.
     * @inheritdoc
     */
    protected function createTemplate() {
        /** @var Nette\Bridges\ApplicationLatte\Template $template Latte šablona pro aktuální presenter. */
        $template = parent::createTemplate();
        $this->translator->createTemplateHelpers()
            ->register($template->getLatte());
        return $template;
    }

    public function startup() {
        $this->getSession()->start();
        parent::startup();
        if ($this->getUser()->isLoggedIn()) {
            $this->administratorEntity = $this->administratorFacade->getAdministrator($this->getUser()->getId());
        }
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->administratorEntity = $this->administratorEntity;
    }
}
