<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\OccupancyIcons;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\OptionEntity;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class OptionsGridWrapper extends GridWrapper {

    /** @var  OptionDao */
    private $optionDao;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /** @var  AdditionEntity */
    private $additionEntity;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * OptionsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param OptionDao $optionDao
     * @param OccupancyIcons $occupancyIcons
     * @param CurrencyDao $currencyDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, OptionDao $optionDao, OccupancyIcons $occupancyIcons, CurrencyDao $currencyDao) {
        parent::__construct($gridWrapperDependencies);
        $this->optionDao = $optionDao;
        $this->occupancyIcons = $occupancyIcons;
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param AdditionEntity $additionEntity
     */
    public function setAdditionEntity(AdditionEntity $additionEntity): void {
         $this->additionEntity = $additionEntity;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->optionDao->getAdditionOptionsGridModel($this->additionEntity));
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendOptionColumns($grid);
        $this->appendPriceColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendOptionColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('capacity', 'Entity.Event.Capacity')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnNumber('occupnacyIcon', 'Entity.Event.OccupancyIcon')
            ->setSortable()
            ->setCustomRender(function(OptionEntity $option){
                return $this->occupancyIcons->getIconHtml($option->getOccupancyIcon());
            });
    }

    protected function appendPriceColumns(Grid $grid){
        foreach ($this->currencyDao->getAllCurrecies() as $currecy) {
            $grid->addColumnNumber('price'.$currecy->getCode(),$this->getTranslator()->translate('Entity.Price.Price').' '.$currecy->getCode(),2)
                ->setCustomRender(function(OptionEntity $optionEntity) use($currecy){
                    $price = $optionEntity->getPrice();
                    if(!$price){
                        return null;
                    }
                    $priceAmount= $price->getPriceAmountByCurrency($currecy);
                    if(!$priceAmount){
                        return null;
                    }
                    return round($priceAmount->getAmount(),2);
                });
        }
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Form.Action.Edit', 'Option:edit');
    }
}