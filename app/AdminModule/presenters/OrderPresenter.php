<?php

namespace App\AdminModule\Presenters;


use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model\Facades\OrderFacade;

class OrderPresenter extends BasePresenter {

    /**
     * @var  OrderFacade
     * @inject
     */
    public $orderFacade;

    /**
     * @var IOrderFormWrapperFactory
     * @inject
     */
    public $orderFormFactory;

    public function actionDefault($id) {

    }

    public function actionEdit($id) {
        $order = $this->orderFacade->getOrder($id);
        if(!$order){
            $this->redirect('Homepage:');
        }
        /** @var OrderFormWrapper $orderForm */
        $orderForm = $this->getComponent('orderForm');
        $orderForm->setOrder($order);
        $this->template->order = $order;
    }

    /**
     * @return \App\Controls\Forms\OrderFormWrapper
     */
    protected function createComponentOrderForm(){
        return $this->orderFormFactory->create();
    }
}
