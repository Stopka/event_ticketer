<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\CurrencyDao;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class CurrenciesGridWrapper extends GridWrapper {

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * CurrenciesGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param CurrencyDao $currencyDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, CurrencyDao $currencyDao) {
        parent::__construct($gridWrapperDependencies);
        $this->currencyDao = $currencyDao;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->currencyDao->getAllCurrenciesGridModel());
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendCurrencyColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendCurrencyColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('code', 'Entity.Currency.Code')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('symbol', 'Entity.Currency.Symbol')
            ->setSortable();
        $grid->addColumnNumber('default', 'Entity.Default')
            ->setSortable()
            ->setReplacement([true=>'Entity.Boolean.Yes',false=>"Entity.Boolean.No"])
            ->setFilterSelect([null=>'', true=>'Entity.Boolean.Yes',false=>"Entity.Boolean.No"]);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Form.Action.Edit','Currency:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionEvent('setDefault','Entity.Default',[$this,'onSetDefaultClicked'])
            ->setIcon('fa fa-check-square');
    }

    public function onSetDefaultClicked(string $id){
        $currency = $this->currencyDao->getCurrency($id);
        $this->currencyDao->setDefaultCurrency($currency);
        $this->redrawControl();
    }
}