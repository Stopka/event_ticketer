<?php

namespace App\Presenters;

use App\Controls\IFlashMessage;
use App\Controls\TFlashTranslatedMessage;
use App\Controls\TInjectTranslator;
use App\Model\Persistence\Dao\AdministratorDao;
use App\Model\Persistence\Entity\AdministratorEntity;
use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter implements IFlashMessage {
    use TInjectTranslator, TFlashTranslatedMessage;

    /** @var string|null */
    public $locale;

    /**
     * @var AdministratorDao
     */
    private $administratorDao;

    /** @var AdministratorEntity Current signed in admin */
    protected $administratorEntity;

    /**
     * @param AdministratorDao $administratorDao
     */
    public function injectAdministratorDao(AdministratorDao $administratorDao) {
        $this->administratorDao = $administratorDao;
    }

    /**
     * Translation macro registration
     * @inheritdoc
     */
    protected function createTemplate() {
        /** @var Nette\Bridges\ApplicationLatte\Template $template Latte templet of current presenter */
        $template = parent::createTemplate();
        $this->getTranslator()->createTemplateHelpers()
            ->register($template->getLatte());
        return $template;
    }

    public function startup() {
        $this->getSession()->start();
        parent::startup();
        if ($this->getUser()->isLoggedIn()) {
            $this->administratorEntity = $this->administratorDao->getAdministrator($this->getUser()->getId());
        }
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->getTemplate()->administratorEntity = $this->administratorEntity;
    }
}
