<?php

namespace App\FrontModule\Presenters;

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
     * @var Model\Facades\EarlyFacade
     * @inject
     */
    public $earlyFacade;

    public function actionDefault($id = null){
        $this->redirect('register',$id);
    }

    public function actionRegister($id = null) {
        $early = $this->earlyFacade->getReadyEarlyByHash($id);
        if(!$early){
            $this->flashMessage('Přístup k registraci nebyl povolen','warning');
            $this->redirect('Homepage:');
        }
        /** @var OrderFormWrapper $orderFormWrapper */
        $orderFormWrapper = $this->getComponent('orderForm');
        $orderFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return OrderFormWrapper
     */
    protected function createComponentOrderForm() {
        return $this->orderFormWrapperFactory->create();
    }

}
