<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Dtos\Uuid;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class CurrenciesGridWrapper extends GridWrapper
{

    private CurrencyDao $currencyDao;

    /**
     * CurrenciesGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param CurrencyDao $currencyDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, CurrencyDao $currencyDao)
    {
        parent::__construct($gridWrapperDependencies);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->currencyDao->getAllCurrenciesGridModel());
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendCurrencyColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendCurrencyColumns(Grid $grid): void
    {
        $grid->addColumnText('name', 'Attribute.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('code', 'Attribute.Currency.Code')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('symbol', 'Attribute.Currency.Symbol')
            ->setSortable();
        $grid->addColumnNumber('default', 'Attribute.Default')
            ->setSortable()
            ->setReplacement([true => 'Value.Boolean.Yes', false => "Value.Boolean.No"])
            ->setFilterSelect([null => '', true => 'Value.Boolean.Yes', false => "Value.Boolean.No"]);
    }


    protected function appendActions(Grid $grid): void
    {
        $grid->addAction('edit', 'Form.Action.Edit', 'Currency:edit')
            ->setIcon('pencil');
        $grid->addActionCallback('setDefault', 'Attribute.Default', [$this, 'onSetDefaultClicked'])
            ->setIcon('check-square');
        $grid->addToolbarButton('Currency:add', 'Presenter.Admin.Currency.Add.H1')
            ->setIcon('plus-circle');
    }

    public function onSetDefaultClicked(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $currency = $this->currencyDao->getCurrency($uuid);
        $this->currencyDao->setDefaultCurrency($currency);
        $this->redrawControl();
    }
}
