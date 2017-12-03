<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\Model\Persistence\Dao\SubstituteDao;


class SubstitutePresenter extends BasePresenter {

    /** @var ICartFormWrapperFactory */
    public $cartFormWrapperFactory;

    /** @var SubstituteDao */
    public $substituteDao;

    /**
     * SubstitutePresenter constructor.
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param SubstituteDao $substituteDao
     */
    public function __construct(ICartFormWrapperFactory $cartFormWrapperFactory, SubstituteDao $substituteDao) {
        parent::__construct();
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
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
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setSubstitute($substitute);
        $event = $substitute->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm() {
        return $this->cartFormWrapperFactory->create();
    }

}
