<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\CartApplicationsGridWrapper;
use App\AdminModule\Controls\Grids\ICartApplicationsGridWrapperFactory;
use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\Model\Persistence\Dao\CartDao;

class CartPresenter extends BasePresenter {

    /** @var CartDao  */
    private $cartDao;

    /** @var ICartFormWrapperFactory  */
    public $cartFormFactory;

    /** @var ICartApplicationsGridWrapperFactory  */
    public $cartApplicationsGridFactory;

    /**
     * CartPresenter constructor.
     * @param CartDao $cartDao
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory
     */
    public function __construct(CartDao $cartDao, ICartFormWrapperFactory $cartFormWrapperFactory, ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory) {
        parent::__construct();
        $this->cartDao = $cartDao;
        $this->cartFormFactory = $cartFormWrapperFactory;
        $this->cartApplicationsGridFactory = $cartApplicationsGridWrapperFactory;
    }


    public function actionDefault($id) {
        $cart = $this->cartDao->getCart($id);
        if(!$cart){
            $this->flashTranslatedMessage('Error.Cart.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        /** @var CartApplicationsGridWrapper $cartApplicationsGrid */
        $cartApplicationsGrid = $this->getComponent('cartApplicationsGrid');
        $cartApplicationsGrid->setCart($cart);
        $this->template->cart = $cart;
    }

    public function actionEdit($id) {
        $cart = $this->cartDao->getCart($id);
        if(!$cart){
            $this->flashTranslatedMessage('Error.Cart.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        /** @var CartFormWrapper $cartForm */
        $cartForm = $this->getComponent('cartForm');
        $cartForm->setCart($cart);
        $this->template->cart = $cart;
    }

    /**
     * @return \App\Controls\Forms\CartFormWrapper
     */
    protected function createComponentCartForm(){
        return $this->cartFormFactory->create();
    }

    protected function createComponentCartApplicationsGrid(){
        return $this->cartApplicationsGridFactory->create();
    }
}
