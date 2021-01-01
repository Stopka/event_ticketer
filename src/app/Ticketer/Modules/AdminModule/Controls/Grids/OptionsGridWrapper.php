<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Exception;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Enums\OptionAutoselectEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\OccupancyIcons;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Daos\OptionDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Ticketer\Model\Database\Managers\OptionManager;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class OptionsGridWrapper extends GridWrapper
{

    private OptionDao $optionDao;

    private OptionManager $optionManager;

    private OccupancyIcons $occupancyIcons;

    private AdditionEntity $additionEntity;

    private CurrencyDao $currencyDao;

    /**
     * OptionsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param OptionManager $optionManager
     * @param OptionDao $optionDao
     * @param OccupancyIcons $occupancyIcons
     * @param CurrencyDao $currencyDao
     */
    public function __construct(
        GridWrapperDependencies $gridWrapperDependencies,
        OptionManager $optionManager,
        OptionDao $optionDao,
        OccupancyIcons $occupancyIcons,
        CurrencyDao $currencyDao
    ) {
        parent::__construct($gridWrapperDependencies);
        $this->optionDao = $optionDao;
        $this->occupancyIcons = $occupancyIcons;
        $this->currencyDao = $currencyDao;
        $this->optionManager = $optionManager;
    }

    /**
     * @param AdditionEntity $additionEntity
     */
    public function setAdditionEntity(AdditionEntity $additionEntity): void
    {
        $this->additionEntity = $additionEntity;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->optionDao->getAdditionOptionsGridModel($this->additionEntity));
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendOptionColumns($grid);
        $this->appendPriceColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendOptionColumns(Grid $grid): void
    {
        $grid->addColumnText('position', 'Attribute.Position')
            ->setSortable()
            ->setSortable()
            ->setSort('ASC');
        $grid->addColumnText('name', 'Attribute.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('capacity', 'Attribute.Event.Capacity')
            ->setSortable()
            ->setFilterRange();
        $grid->addColumnNumber('occupnacyIcon', 'Attribute.Event.OccupancyIcon')
            ->setSortable()
            ->setRenderer(
                function (OptionEntity $option): Html {
                    $icon = $option->getOccupancyIcon();
                    if (null === $icon) {
                        return Html::el();
                    }

                    return $this->occupancyIcons->getLabel($icon);
                }
            );
        $grid->addColumnNumber('autoselect', 'Attribute.Addition.AutoSelect')
            ->setSortable()
            ->setReplacement(
                [
                    OptionAutoselectEnum::NONE => "Value.Addition.AutoSelect.None",
                    OptionAutoselectEnum::ALWAYS => "Value.Addition.AutoSelect.Always",
                    OptionAutoselectEnum::SECOND_ON => "Value.Addition.AutoSelect.SecondOn",
                ]
            )
            ->setFilterSelect(
                [
                    null => "",
                    OptionAutoselectEnum::NONE => "Value.Addition.AutoSelect.None",
                    OptionAutoselectEnum::ALWAYS => "Value.Addition.AutoSelect.Always",
                    OptionAutoselectEnum::SECOND_ON => "Value.Addition.AutoSelect.SecondOn",
                ]
            );
    }

    protected function appendPriceColumns(Grid $grid): void
    {
        foreach ($this->currencyDao->getAllCurrecies() as $currecy) {
            $grid->addColumnNumber(
                'price' . $currecy->getCode(),
                $this->getTranslator()->translate('Entity.Singular.Price') . ' ' . $currecy->getCode()
            )
                ->setFormat(2)
                ->setRenderer(
                    function (OptionEntity $optionEntity) use ($currecy): ?float {
                        $price = $optionEntity->getPrice();
                        if (null === $price) {
                            return null;
                        }
                        $priceAmount = $price->getPriceAmountByCurrency($currecy);
                        if (null === $priceAmount) {
                            return null;
                        }

                        return $priceAmount->getAmount();
                    }
                );
        }
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function appendActions(Grid $grid): void
    {
        $grid->addAction('edit', 'Form.Action.Edit', 'Option:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionCallback('moveUp', 'Form.Action.MoveUp', [$this, 'onMoveUpClicked'])
            ->setIcon('fa fa-arrow-up');
        $grid->addActionCallback('moveDown', 'Form.Action.MoveDown', [$this, 'onMoveDownClicked'])
            ->setIcon('fa fa-arrow-down');
        $grid->addActionCallback('delete', 'Form.Action.Delete', [$this, 'onDeleteClicked'])
            ->setIcon('fa fa-trash')
            ->setConfirmation(
                new CallbackConfirmation(
                    function (OptionEntity $optionEntity): string {
                        return $this->getTranslator()->translate(
                            'Form.Entity.Message.Delete.Confirm',
                            null,
                            [
                                'name' => $optionEntity->getName(),
                            ]
                        );
                    }
                )
            );
        $grid->addToolbarButton(
            'Option:add',
            'Presenter.Admin.Option.Add.H1',
            [$this->additionEntity->getId()->toString()]
        )
            ->setIcon('fa fa-plus-circle');
    }

    /**
     * @param string $optionId
     * @throws Exception
     * @throws AbortException
     */
    public function onMoveUpClicked(string $optionId): void
    {
        $optionUuid = Uuid::fromString($optionId);
        $option = $this->optionDao->getOption($optionUuid);
        if (null === $option) {
            return;
        }
        $this->optionManager->moveOptionUp($option);
        $this->flashTranslatedMessage('Form.Message.MoveUp.Success');
        $this->redirect('this');
    }

    /**
     * @param string $optionId
     * @throws Exception
     * @throws AbortException
     */
    public function onMoveDownClicked(string $optionId): void
    {
        $optionUuid = Uuid::fromString($optionId);
        $option = $this->optionDao->getOption($optionUuid);
        if (null === $option) {
            return;
        }
        $this->optionManager->moveOptionDown($option);
        $this->flashTranslatedMessage('Form.Message.MoveDown.Success');
        $this->redirect('this');
    }

    /**
     * @param string $optionId
     * @throws AbortException
     */
    public function onDeleteClicked(string $optionId): void
    {
        $optionUuid = Uuid::fromString($optionId);
        $option = $this->optionDao->getOption($optionUuid);
        if (null === $option) {
            return;
        }
        $this->optionManager->deleteOption($option);
        $this->flashTranslatedMessage('Form.Message.Delete.Success');
        $this->redirect('this');
    }
}
