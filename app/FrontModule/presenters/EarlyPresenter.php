<?php

namespace App\FrontModule\Presenters;

use App\CompanyModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\CompanyModule\Controls\Forms\SubstituteFormWrapper;
use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model;


class EarlyPresenter extends BasePresenter {

    /**
     * @var IOrderFormWrapperFactory
     * @inject
     */
    public $orderFormWrapperFactory;

    /**
     * @var ISubstituteFormWrapperFactory
     * @inject
     */
    public $substituteFormWrapperFactory;

    /**
     * @var Model\Facades\ApplicationFacade
     * @inject
     */
    public $applicationFacade;

    /**
     * @var Model\Facades\EarlyFacade
     * @inject
     */
    public $earlyFacade;

    public function actionDefault($id = null) {
        $this->redirect('register', $id);
    }

    public function actionRegister($id = null) {
        $early = $this->earlyFacade->getReadyEarlyByHash($id);
        if (!$early) {
            $this->flashMessage('Přístup k registraci nebyl povolen', 'warning');
            $this->redirect('Homepage:');
        }
        /** @var OrderFormWrapper $orderFormWrapper */
        $orderFormWrapper = $this->getComponent('orderForm');
        $orderFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if ($event->isCapacityFull($this->applicationFacade->countIssuedApplications($event))) {
            $this->flashMessage('Již nejsou žádné volné přihlášky.', 'warning');
            $this->redirect('substitute', $id);
        }
        $this->template->event = $event;
    }

    public function actionSubstitute($id = null) {
        $early = $this->earlyFacade->getReadyEarlyByHash($id);
        if (!$early) {
            $this->flashMessage('Přístup k registraci nebyl povolen', 'warning');
            $this->redirect('Homepage:');
        }
        /** @var OrderFormWrapper $orderFormWrapper */
        $substituteFormWrapper = $this->getComponent('substituteForm');
        $substituteFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if (!$event->isCapacityFull($this->applicationFacade->countIssuedApplications($event))) {
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
