<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Exception;
use Nette\Application\AbortException;
use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Daos\AdditionDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\AdditionManager;
use Ticketer\Model\Dtos\Uuid;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;
use Ublaboo\DataGrid\Exception\DataGridException;

class AdditionsGridWrapper extends GridWrapper
{

    private AdditionDao $additionDao;

    private EventEntity $eventEntity;

    private AdditionManager $additionManager;

    /**
     * AdditionsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param AdditionManager $additionManager
     * @param AdditionDao $additionDao
     */
    public function __construct(
        GridWrapperDependencies $gridWrapperDependencies,
        AdditionManager $additionManager,
        AdditionDao $additionDao
    ) {
        parent::__construct($gridWrapperDependencies);
        $this->additionDao = $additionDao;
        $this->additionManager = $additionManager;
    }

    /**
     * @param EventEntity $eventEntity
     */
    public function setEventEntity(EventEntity $eventEntity): void
    {
        $this->eventEntity = $eventEntity;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->additionDao->getEventAdditionsGridModel($this->eventEntity));
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendAdditionColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendAdditionColumns(Grid $grid): void
    {
        $grid->addColumnText('position', 'Attribute.Position')
            ->setSortable()
            ->setSort('ASC');
        $grid->addColumnText('name', 'Attribute.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('minimum', 'Attribute.Addition.Minimum')
            ->setSortable()
            ->setFilterRange();
        $grid->addColumnNumber('maximum', 'Attribute.Addition.Maximum')
            ->setSortable()
            ->setFilterRange();
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function appendActions(Grid $grid): void
    {
        $grid->addAction('edit', 'Form.Action.Edit', 'Addition:edit')
            ->setIcon('fa fa-pencil');
        $grid->addAction('options', 'Entity.Plural.Option', 'Option:')
            ->setIcon('fa fa-list-ul');
        $grid->addActionCallback('moveUp', 'Form.Action.MoveUp', [$this, 'onMoveUpClicked'])
            ->setIcon('fa fa-arrow-up');
        $grid->addActionCallback('moveDown', 'Form.Action.MoveDown', [$this, 'onMoveDownClicked'])
            ->setIcon('fa fa-arrow-down');
        $grid->addActionCallback('delete', 'Form.Action.Delete', [$this, 'onDeleteClicked'])
            ->setIcon('fa fa-trash')
            ->setConfirmation(
                new CallbackConfirmation(
                    function (AdditionEntity $additionEntity): string {
                        return $this->getTranslator()->translate(
                            'Form.Entity.Message.Delete.Confirm',
                            null,
                            [
                                'name' => $additionEntity->getName(),
                            ]
                        );
                    }
                )
            );
        $grid->addToolbarButton(
            "Addition:add",
            "Presenter.Admin.Addition.Add.H1",
            ['id' => $this->eventEntity->getId()->toString()]
        )
            ->setIcon('fa fa-plus-circle');
    }

    /**
     * @param string $additionId
     * @throws Exception
     * @throws AbortException
     */
    public function onMoveUpClicked(string $additionId): void
    {
        $additionUuid = Uuid::fromString($additionId);
        $addition = $this->additionDao->getAddition($additionUuid);
        if (null === $addition) {
            return;
        }
        $this->additionManager->moveAdditionUp($addition);
        $this->flashTranslatedMessage('Form.Message.MoveUp.Success');
        $this->redirect('this');
    }

    /**
     * @param string $additionId
     * @throws AbortException
     */
    public function onMoveDownClicked(string $additionId): void
    {
        $additionUuid = Uuid::fromString($additionId);
        $addition = $this->additionDao->getAddition($additionUuid);
        if (null === $addition) {
            return;
        }
        $this->additionManager->moveAdditionDown($addition);
        $this->flashTranslatedMessage('Form.Message.MoveDown.Success');
        $this->redirect('this');
    }

    /**
     * @param string $additionId
     * @throws AbortException
     */
    public function onDeleteClicked(string $additionId): void
    {
        $additionUuid = Uuid::fromString($additionId);
        $addition = $this->additionDao->getAddition($additionUuid);
        if (null === $addition) {
            return;
        }
        $this->additionManager->deleteAddition($addition);
        $this->flashTranslatedMessage('Form.Message.Delete.Success');
        $this->redirect('this');
    }
}
