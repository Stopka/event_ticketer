<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model\Persistence\Dao\SubstituteDao;


class SubstitutePresenter extends BasePresenter {

    /** @var IOrderFormWrapperFactory */
    public $orderFormWrapperFactory;

    /** @var SubstituteDao */
    public $substituteDao;

    /**
     * SubstitutePresenter constructor.
     * @param IOrderFormWrapperFactory $orderFormWrapperFactory
     * @param SubstituteDao $substituteDao
     */
    public function __construct(IOrderFormWrapperFactory $orderFormWrapperFactory, SubstituteDao $substituteDao) {
        parent::__construct();
        $this->orderFormWrapperFactory = $orderFormWrapperFactory;
        $this->substituteDao = $substituteDao;
    }


    public function actionDefault($id = null) {
        $this->redirect('register', $id);
    }

    public function actionRegister($id = null) {
        $substitute = $this->substituteDao->getReadySubstitute($id);
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
