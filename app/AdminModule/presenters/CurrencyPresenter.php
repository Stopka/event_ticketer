<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\CurrencyFormWrapper;
use App\AdminModule\Controls\Forms\ICurrencyFromWrapperFactory;
use App\AdminModule\Controls\Grids\CurrenciesGridWrapper;
use App\AdminModule\Controls\Grids\ICurrenciesGridWrapperFactory;
use App\Model\Persistence\Dao\CurrencyDao;

class CurrencyPresenter extends BasePresenter {

    /** @var  ICurrenciesGridWrapperFactory */
    private $currenciesGridWrapperFactory;

    /** @var  ICurrencyFromWrapperFactory */
    private $currencyFormWrapperFactory;

    /** @var  CurrencyDao */
    private $currencyDao;

    public function __construct(CurrencyDao $currencyDao, ICurrenciesGridWrapperFactory $currenciesGridWrapperFactory, ICurrencyFromWrapperFactory $currencyFromWrapperFactory) {
        parent::__construct();
        $this->currencyDao = $currencyDao;
        $this->currenciesGridWrapperFactory = $currenciesGridWrapperFactory;
        $this->currencyFormWrapperFactory = $currencyFromWrapperFactory;
    }

    public function actionDefault() {

    }

    public function actionAdd() {
        $this->redirect("edit");
    }

    public function actionEdit($id = null) {
        $currency = $this->currencyDao->getCurrency($id);
        if (!$currency && $id) {
            $this->redirect('edit');
        }
        /** @var CurrencyFormWrapper $currencyForm */
        $currencyForm = $this->getComponent('currencyForm');
        $currencyForm->setCurrencyEntity($currency);
        $this->template->currency = $currency;
    }

    /**
     * @return CurrenciesGridWrapper
     */
    public function createComponentCurrenciesGrid(){
        return $this->currenciesGridWrapperFactory->create();
    }

    /**
     * @return CurrencyFormWrapper
     */
    public function createComponentCurrencyForm(){
        return $this->currencyFormWrapperFactory->create();
    }

}
