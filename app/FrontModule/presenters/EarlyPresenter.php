<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\FrontModule\Controls\Forms\SubstituteFormWrapper;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EarlyDao;


class EarlyPresenter extends BasePresenter {

    /** @var IOrderFormWrapperFactory */
    public $orderFormWrapperFactory;

    /** @var ISubstituteFormWrapperFactory */
    public $substituteFormWrapperFactory;

    /** @var ApplicationDao */
    public $applicationDao;

    /** @var EarlyDao */
    public $earlyDao;

    /**
     * EarlyPresenter constructor.
     * @param IOrderFormWrapperFactory $orderFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param ApplicationDao $applicationDao
     * @param EarlyDao $earlyDao
     */
    public function __construct(IOrderFormWrapperFactory $orderFormWrapperFactory, ISubstituteFormWrapperFactory $substituteFormWrapperFactory, ApplicationDao $applicationDao, EarlyDao $earlyDao) {
        parent::__construct();
        $this->orderFormWrapperFactory = $orderFormWrapperFactory;
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
        /** @var OrderFormWrapper $orderFormWrapper */
        $orderFormWrapper = $this->getComponent('orderForm');
        $orderFormWrapper->setEarly($early);
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
        /** @var OrderFormWrapper $orderFormWrapper */
        $substituteFormWrapper = $this->getComponent('substituteForm');
        $substituteFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if (!$event->isCapacityFull()) {
            $this->redirect('register', $id);
        }
        $this->template->event = $event;
    }

    /**
     * @return OrderFormWrapper
     */
    protected function createComponentOrderForm() {
        return $this->orderFormWrapperFactory->create();
    }

    /**
     * @return SubstituteFormWrapper
     */
    protected function createComponentSubstituteForm() {
        return $this->substituteFormWrapperFactory->create();
    }

}
