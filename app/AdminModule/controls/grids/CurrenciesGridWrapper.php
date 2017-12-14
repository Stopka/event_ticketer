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
        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('code', 'Kód')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('symbol', 'Symbol')
            ->setSortable();
        $grid->addColumnNumber('default', 'Výchozí')
            ->setSortable()
            ->setReplacement([true=>'Ano',false=>"Ne"])
            ->setFilterSelect([null=>'', true=>'Ano',false=>"Ne"]);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Upravit','Currency:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionEvent('setDefault','Výchozí',[$this,'onSetDefaultClicked'])
            ->setIcon('fa fa-check-square');
    }

    public function onSetDefaultClicked(string $id){
        $currency = $this->currencyDao->getCurrency($id);
        $this->currencyDao->setDefaultCurrency($currency);
        $this->redrawControl();
    }
}