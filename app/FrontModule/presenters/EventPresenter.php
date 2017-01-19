<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model;


class EventPresenter extends BasePresenter {

    /**
     * @var Model\Facades\EventFacade
     * @inject
     */
    public $eventFacade;

    /**
     * @var IOrderFormWrapperFactory
     * @inject
     */
    public $orderFormWrapperFactory;

    public function actionDefault($id = null){
        $this->redirect('register',$id);
    }

    public function renderRegister($id = null) {
        $event = $this->eventFacade->getPublicAvailibleEvent($id);
        if(!$event){
            $this->flashMessage('Událost nebyla nalezena','warning');
            $this->redirect('Homepage:');
        }
        /** @var OrderFormWrapper $orderForm */
        $orderForm = $this->getComponent('orderForm');
        $orderForm->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @return OrderFormWrapper
     */
    protected function createComponentOrderForm(){
        return $this->orderFormWrapperFactory->create();
    }

}
