<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\OccupancyIcons;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Manager\OptionManager;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class OptionsGridWrapper extends GridWrapper {

    /** @var  OptionDao */
    private $optionDao;

    /** @var OptionManager */
    private $optionManager;

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
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, OptionManager $optionManager, OptionDao $optionDao, OccupancyIcons $occupancyIcons, CurrencyDao $currencyDao) {
        parent::__construct($gridWrapperDependencies);
        $this->optionDao = $optionDao;
        $this->occupancyIcons = $occupancyIcons;
        $this->currencyDao = $currencyDao;
        $this->optionManager = $optionManager;
    }

    /**
     * @param AdditionEntity $additionEntity
     */
    public function setAdditionEntity(AdditionEntity $additionEntity): void {
        $this->additionEntity = $additionEntity;
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function loadModel(Grid $grid) {
        $grid->setModel($this->optionDao->getAdditionOptionsGridModel($this->additionEntity));
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendOptionColumns($grid);
        $this->appendPriceColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendOptionColumns(Grid $grid) {
        $grid->addColumnText('position', 'Entity.Position')
            ->setSortable()
            ->setDefaultSort('ASC');
        $grid->addColumnText('name', 'Entity.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('capacity', 'Entity.Event.Capacity')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnNumber('occupnacyIcon', 'Entity.Event.OccupancyIcon')
            ->setSortable()
            ->setCustomRender(function (OptionEntity $option) {
                return $this->occupancyIcons->getIconHtml($option->getOccupancyIcon());
            });
        $grid->addColumnNumber('autoselect', 'Entity.Addition.AutoSelect.AutoSelect')
            ->setSortable()
            ->setReplacement([
                OptionEntity::AUTOSELECT_NONE => "Entity.Addition.AutoSelect.None",
                OptionEntity::AUTOSELECT_ALWAYS => "Entity.Addition.AutoSelect.Always",
                OptionEntity::AUTOSELECT_SECONDON => "Entity.Addition.AutoSelect.SecondOn",
            ])
            ->setFilterSelect([
                null => "",
                OptionEntity::AUTOSELECT_NONE => "Entity.Addition.AutoSelect.None",
                OptionEntity::AUTOSELECT_ALWAYS => "Entity.Addition.AutoSelect.Always",
                OptionEntity::AUTOSELECT_SECONDON => "Entity.Addition.AutoSelect.SecondOn",
            ]);
    }

    protected function appendPriceColumns(Grid $grid) {
        foreach ($this->currencyDao->getAllCurrecies() as $currecy) {
            $grid->addColumnNumber('price' . $currecy->getCode(), $this->getTranslator()->translate('Entity.Price.Price') . ' ' . $currecy->getCode(), 2)
                ->setCustomRender(function (OptionEntity $optionEntity) use ($currecy) {
                    $price = $optionEntity->getPrice();
                    if (!$price) {
                        return null;
                    }
                    $priceAmount = $price->getPriceAmountByCurrency($currecy);
                    if (!$priceAmount) {
                        return null;
                    }
                    return round($priceAmount->getAmount(), 2);
                });
        }
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit', 'Form.Action.Edit', 'Option:edit');
        $grid->addActionEvent('moveUp', 'Form.Action.MoveUp', [$this, 'onMoveUpClicked']);
        $grid->addActionEvent('moveDown', 'Form.Action.MoveDown', [$this, 'onMoveDownClicked']);
    }

    /**
     * @param string $optionId
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function onMoveUpClicked(string $optionId) {
        $option = $this->optionDao->getOption($optionId);
        if (!$option) {
            return;
        }
        $this->optionManager->moveOptionUp($option);
        $this->flashTranslatedMessage('Entity.Message.MoveUp.Success');
        $this->redirect('this');
    }

    /**
     * @param string $optionId
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function onMoveDownClicked(string $optionId) {
        $option = $this->optionDao->getOption($optionId);
        if (!$option) {
            return;
        }
        $this->optionManager->moveOptionDown($option);
        $this->flashTranslatedMessage('Entity.Message.MoveDown.Success');
        $this->redirect('this');
    }
}