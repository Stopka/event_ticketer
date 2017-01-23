<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Grids\IOrderApplicationsGridWrapperFactory;
use App\FrontModule\Controls\Grids\OrderApplicationsGridWrapper;
use App\Model;


class OrderPresenter extends BasePresenter {

    /**
     * @var Model\Facades\OrderFacade
     * @inject
     */
    public $orderFacade;

    /**
     * @var IOrderApplicationsGridWrapperFactory
     * @inject
     */
    public $orderApplicationsGridWrapperFactory;

    public function actionDefault($id = null) {
        $order = $this->orderFacade->getViewableOrderByHash($id);
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
