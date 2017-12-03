<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\FrontModule\Controls\Forms\SubstituteFormWrapper;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EarlyDao;


class EarlyPresenter extends BasePresenter {

    /** @var ICartFormWrapperFactory */
    public $cartFormWrapperFactory;

    /** @var ISubstituteFormWrapperFactory */
    public $substituteFormWrapperFactory;

    /** @var ApplicationDao */
    public $applicationDao;

    /** @var EarlyDao */
    public $earlyDao;

    /**
     * EarlyPresenter constructor.
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param ApplicationDao $applicationDao
     * @param EarlyDao $earlyDao
     */
    public function __construct(ICartFormWrapperFactory $cartFormWrapperFactory, ISubstituteFormWrapperFactory $substituteFormWrapperFactory, ApplicationDao $applicationDao, EarlyDao $earlyDao) {
        parent::__construct();
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
        $this->applicationDao = $applicationDao;
        $this->earlyDao = $earlyDao;
    }


    public function actionDefault($id = null) {
        $this->redirect('register', $id);
    }

    public function actionRegister($id = null) {
        $early = $this->earlyDao->getReadyEarlyByHash($id);
        if (!$early) {
            $this->flashMessage('Přístup k registraci nebyl povolen', 'warning');
            $this->redirect('Homepage:');
        }
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if ($event->isCapacityFull()) {
            $this->flashMessage('Již nejsou žádné volné přihlášky.', 'warning');
            $this->redirect('substitute', $id);
        }
        $this->template->event = $event;
    }

    public function actionSubstitute($id = null) {
        $early = $this->earlyDao->getReadyEarlyByHash($id);
        if (!$early) {
            $this->flashMessage('Přístup k registraci nebyl povolen', 'warning');
            $this->redirect('Homepage:');
        }
        /** @var SubstituteFormWrapper $substituteFormWrapper */
        $substituteFormWrapper = $this->getComponent('substituteForm');
        $substituteFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if (!$event->isCapacityFull()) {
            $this->redirect('register', $id);
        }
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm() {
        return $this->cartFormWrapperFactory->create();
    }

    /**
     * @return SubstituteFormWrapper
     */
    protected function createComponentSubstituteForm() {
        return $this->substituteFormWrapperFactory->create();
    }

}
