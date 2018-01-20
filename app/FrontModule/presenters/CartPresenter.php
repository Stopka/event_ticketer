<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Controls\Grids\CartApplicationsGridWrapper;
use App\FrontModule\Controls\Grids\ICartApplicationsGridWrapperFactory;
use App\Model\Persistence\Dao\CartDao;


class CartPresenter extends BasePresenter {

    /** @var CartDao */
    public $cartDao;

    /** @var ICartApplicationsGridWrapperFactory */
    public $cartApplicationsGridWrapperFactory;

    public function __construct(CartDao $cartDao, ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory) {
        parent::__construct();
        $this->cartDao = $cartDao;
        $this->cartApplicationsGridWrapperFactory = $cartApplicationsGridWrapperFactory;
    }

    /**
     * @param string $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(string $id) {
        $cart = $this->cartDao->getViewableCart($id);
        if(!$cart){
            $this->flashTranslatedMessage('Error.Cart.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        /** @var CartApplicationsGridWrapper $applicationsGrid */
        $applicationsGrid = $this->getComponent('applicationsGrid');
        $applicationsGrid->setCart($cart);
        $this->template->cart = $cart;
    }

    /**
     * @return \App\FrontModule\Controls\Grids\CartApplicationsGridWrapper
     */
    protected function createComponentApplicationsGrid(){
        return $this->cartApplicationsGridWrapperFactory->create();
    }


}
