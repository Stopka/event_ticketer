<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\IOrderApplicationsGridWrapperFactory;
use App\AdminModule\Controls\Grids\OrderApplicationsGridWrapper;
use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model\Persistence\Dao\OrderDao;

class OrderPresenter extends BasePresenter {

    /** @var OrderDao  */
    private $orderDao;

    /** @var IOrderFormWrapperFactory  */
    public $orderFormFactory;

    /** @var IOrderApplicationsGridWrapperFactory  */
    public $orderApplicationsGridFactory;

    /**
     * OrderPresenter constructor.
     * @param OrderDao $orderDao
     * @param IOrderFormWrapperFactory $orderFormWrapperFactory
     * @param IOrderApplicationsGridWrapperFactory $orderApplicationsGridWrapperFactory
     */
    public function __construct(OrderDao $orderDao, IOrderFormWrapperFactory $orderFormWrapperFactory, IOrderApplicationsGridWrapperFactory $orderApplicationsGridWrapperFactory) {
        parent::__construct();
        $this->orderDao = $orderDao;
        $this->orderFormFactory = $orderFormWrapperFactory;
        $this->orderApplicationsGridFactory = $orderApplicationsGridWrapperFactory;
    }


    public function actionDefault($id) {
        $order = $this->orderDao->getOrder($id);
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
        $order = $this->orderDao->getOrder($id);
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
