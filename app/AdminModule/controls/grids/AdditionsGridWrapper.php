<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Entity\EventEntity;

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

    /**
     * AdditionsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param AdditionDao $additionDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, AdditionDao $additionDao) {
        parent::__construct($gridWrapperDependencies);
        $this->additionDao = $additionDao;
    }

    /**
     * @param EventEntity $eventEntity
     */
    public function setEventEntity(EventEntity $eventEntity): void {
         $this->eventEntity = $eventEntity;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->additionDao->getEventAdditionsGridModel($this->eventEntity));
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendAdditionColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendAdditionColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('minimum', 'Entity.Addition.Minimum')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnNumber('maximum', 'Entity.Addition.Maximum')
            ->setSortable()
            ->setFilterNumber();
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Form.Action.Edit', 'Addition:edit')
            ->setIcon('fa fa-pencil-o');
        $grid->addActionHref('options','Entity.Addition.Options', 'Option:')
            ->setIcon('fa fa-list-ul');
    }
}