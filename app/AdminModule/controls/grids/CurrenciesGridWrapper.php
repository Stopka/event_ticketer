<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\Persistence\Dao\CurrencyDao;
use Nette\Localization\ITranslator;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class CurrenciesGridWrapper extends GridWrapper {

    /** @var  CurrencyDao */
    private $currencyDao;

    public function __construct(ITranslator $translator, CurrencyDao $currencyDao) {
        parent::__construct($translator);
        $this->currencyDao = $currencyDao;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->currencyDao->getAllCurrenciesGridModel());
    }

    protected function configure(\App\Grids\Grid $grid) {
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