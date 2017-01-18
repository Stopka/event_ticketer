<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\OrderFormFactory;
use App\Model;


class EarlyPresenter extends BasePresenter {

    /**
     * @var OrderFormFactory
     * @inject
     */
    public $orderFormFactory;

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
        $this->orderFormFactory->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        $this->template->event = $event;
    }

    protected function createComponentOrderForm() {
        return $this->orderFormFactory->create();
    }

}
