<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\IOrderApplicationsGridWrapperFactory;
use App\AdminModule\Controls\Grids\OrderApplicationsGridWrapper;
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

    /**
     * @var IOrderApplicationsGridWrapperFactory
     * @inject
     */
    public $orderApplicationsGridFactory;

    public function actionDefault($id) {
        $order = $this->orderFacade->getOrder($id);
        if(!$order){
            $this->flashMessage('Objednávka nebyla nalezena','error');
            $this->redirect('Homepage:');
        }
        /** @var OrderApplicationsGridWrapper $orderApplicationsGrid */
        $orderApplicationsGrid = $this->getComponent('orderApplicationsGrid');
        $orderApplicationsGrid->setOrder($order);
        $this->template->order = $order;
    }

    public function actionEdit($id) {
        $order = $this->orderFacade->getOrder($id);
        if(!$order){
            $this->flashMessage('Objednávka nebyla nalezena','error');
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

    protected function createComponentOrderApplicationsGrid(){
        return $this->orderApplicationsGridFactory->create();
    }
}
