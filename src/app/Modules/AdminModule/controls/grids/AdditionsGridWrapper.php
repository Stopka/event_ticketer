<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\AdditionManager;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class AdditionsGridWrapper extends GridWrapper {

    /** @var  AdditionDao */
    private $additionDao;

    /** @var  EventEntity */
    private $eventEntity;

    /** @var AdditionManager */
    private $additionManager;

    /**
     * AdditionsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param AdditionManager $additionManager
     * @param AdditionDao $additionDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, AdditionManager $additionManager, AdditionDao $additionDao) {
        parent::__construct($gridWrapperDependencies);
        $this->additionDao = $additionDao;
        $this->additionManager = $additionManager;
    }

    /**
     * @param EventEntity $eventEntity
     */
    public function setEventEntity(EventEntity $eventEntity): void {
        $this->eventEntity = $eventEntity;
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function loadModel(Grid $grid) {
        $grid->setModel($this->additionDao->getEventAdditionsGridModel($this->eventEntity));
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendAdditionColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendAdditionColumns(Grid $grid) {
        $grid->addColumnText('position', 'Attribute.Position')
            ->setSortable()
            ->setDefaultSort('ASC');
        $grid->addColumnText('name', 'Attribute.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('minimum', 'Attribute.Addition.Minimum')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnNumber('maximum', 'Attribute.Addition.Maximum')
            ->setSortable()
            ->setFilterNumber();
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit', 'Form.Action.Edit', 'Addition:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionHref('options', 'Entity.Plural.Option', 'Option:')
            ->setIcon('fa fa-list-ul');
        $grid->addActionEvent('moveUp', 'Form.Action.MoveUp', [$this, 'onMoveUpClicked'])
            ->setIcon('fa fa-arrow-up');
        $grid->addActionEvent('moveDown', 'Form.Action.MoveDown', [$this, 'onMoveDownClicked'])
            ->setIcon('fa fa-arrow-down');
        $grid->addActionEvent('delete', 'Form.Action.Delete', [$this, 'onDeleteClicked'])
            ->setIcon('fa fa-trash')
            ->setConfirm(function (AdditionEntity $additionEntity) {
                return $this->getTranslator()->translate('Form.Entity.Message.Delete.Confirm', null, [
                    'name' => $additionEntity->getName()
                ]);
            });
        $grid->addButton('add', "Presenter.Admin.Addition.Add.H1", "Addition:add", ['id' => $this->eventEntity->getId()])
            ->setIcon('fa fa-plus-circle');
    }

    /**
     * @param string $additionId
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function onMoveUpClicked(string $additionId) {
        $addition = $this->additionDao->getAddition($additionId);
        if (!$addition) {
            return;
        }
        $this->additionManager->moveAdditionUp($addition);
        $this->flashTranslatedMessage('Form.Message.MoveUp.Success');
        $this->redirect('this');
    }

    /**
     * @param string $addditionId
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function onMoveDownClicked(string $addditionId) {
        $addition = $this->additionDao->getAddition($addditionId);
        if (!$addition) {
            return;
        }
        $this->additionManager->moveAdditionDown($addition);
        $this->flashTranslatedMessage('Form.Message.MoveDown.Success');
        $this->redirect('this');
    }

    /**
     * @param string $addditionId
     * @throws \Nette\Application\AbortException
     */
    public function onDeleteClicked(string $addditionId) {
        $addition = $this->additionDao->getAddition($addditionId);
        if (!$addition) {
            return;
        }
        $this->additionManager->deleteAddition($addition);
        $this->flashTranslatedMessage('Form.Message.Delete.Success');
        $this->redirect('this');
    }
}