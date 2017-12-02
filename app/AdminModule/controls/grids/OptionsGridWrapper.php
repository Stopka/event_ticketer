<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\OccupancyIcons;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Nette\Localization\ITranslator;

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

    public function __construct(ITranslator $translator, OptionDao $optionDao, OccupancyIcons $occupancyIcons, CurrencyDao $currencyDao) {
        parent::__construct($translator);
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

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendOptionColumns($grid);
        $this->appendPriceColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendOptionColumns(Grid $grid) {
        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('capacity', 'Kapacita')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnNumber('occupnacyIcon', 'Ikona obsazenosti')
            ->setSortable()
            ->setCustomRender(function(OptionEntity $option){
                return $this->occupancyIcons->getIconHtml($option->getOccupancyIcon());
            });
    }

    protected function appendPriceColumns(Grid $grid){
        foreach ($this->currencyDao->getAllCurrecies() as $currecy) {
            $grid->addColumnNumber('price'.$currecy->getCode(),'Cena '.$currecy->getCode(),2)
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
        $grid->addActionHref('edit','Upravit', 'Option:edit');
    }
}