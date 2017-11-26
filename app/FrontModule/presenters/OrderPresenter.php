<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Grids\IOrderApplicationsGridWrapperFactory;
use App\FrontModule\Controls\Grids\OrderApplicationsGridWrapper;
use App\Model\Persistence\Dao\OrderDao;


class OrderPresenter extends BasePresenter {

    /** @var OrderDao */
    public $orderDao;

    /** @var IOrderApplicationsGridWrapperFactory */
    public $orderApplicationsGridWrapperFactory;

    public function __construct(OrderDao $orderDao, IOrderApplicationsGridWrapperFactory $orderApplicationsGridWrapperFactory) {
        parent::__construct();
        $this->orderDao = $orderDao;
        $this->orderApplicationsGridWrapperFactory = $orderApplicationsGridWrapperFactory;
    }


    public function actionDefault($id = null) {
        $order = $this->orderDao->getViewableOrder($id);
        if(!$order){
            $this->flashMessage('Objednávka nebyla nalezena','error');
            $this->redirect('Homepage:');
        }
        /** @var OrderApplicationsGridWrapper $applicationsGrid */
        $applicationsGrid = $this->getComponent('applicationsGrid');
        $applicationsGrid->setOrder($order);
        $this->template->order = $order;
    }

    /**
     * @return \App\FrontModule\Controls\Grids\OrderApplicationsGridWrapper
     */
    protected function createComponentApplicationsGrid(){
        return $this->orderApplicationsGridWrapperFactory->create();
    }


}
