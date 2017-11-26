<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model;


class SubstitutePresenter extends BasePresenter {

    /**
     * @var IOrderFormWrapperFactory
     * @inject
     */
    public $orderFormWrapperFactory;

    /**
     * @var Model\Facades\SubstituteDao
     * @inject
     */
    public $substituteFacade;

    public function actionDefault($id = null) {
        $this->redirect('register', $id);
    }

    public function actionRegister($id = null) {
        $substitute = $this->substituteFacade->getReadySubstitute($id);
        if (!$substitute) {
            $this->flashMessage('Náhradníkovo místo vypršelo nebo nebylo nalezeno', 'warning');
            $this->redirect('Homepage:');
        }
        /** @var OrderFormWrapper $orderFormWrapper */
        $orderFormWrapper = $this->getComponent('orderForm');
        $orderFormWrapper->setSubstitute($substitute);
        $event = $substitute->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return OrderFormWrapper
     */
    protected function createComponentOrderForm() {
        return $this->orderFormWrapperFactory->create();
    }

}
