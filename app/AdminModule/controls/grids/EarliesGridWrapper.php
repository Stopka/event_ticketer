<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\EarlyDao;
use App\Model\Persistence\Entity\EventEntity;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class EarliesGridWrapper extends GridWrapper {

    /** @var  EarlyDao */
    private $earlyDao;

    /** @var  EventEntity */
    private $eventEntity;

    /**
     * EarliesGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param EarlyDao $earlyDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, EarlyDao $earlyDao) {
        parent::__construct($gridWrapperDependencies);
        $this->earlyDao = $earlyDao;
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
        $grid->setModel($this->earlyDao->getEventEarliesGridModel($this->eventEntity));
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendEarlyColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyColumns(Grid $grid) {
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('lastName', 'Attribute.Person.LastName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('email', 'Attribute.Person.Email')
            ->setSortable()
            ->setFilterText();
    }

    protected function appendEarlyWaveColumns(Grid $grid) {
        $grid->addColumnText('earlyWave.name', 'Entity.Singular.EarlyWave')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('earlyWave.startDate', 'Attribute.Event.StartDate')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('earlyWave.inviteSent', 'Attribute.Event.InviteSent')
            ->setReplacement([true => 'Value.Boolean.Yes', false => 'Value.Boolean.No'])
            ->setSortable()
            ->setFilterSelect([null => '', true => 'Value.Boolean.Yes', false => 'Value.Boolean.No']);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit', 'Form.Action.Edit', 'Early:edit')
            ->setIcon('fa fa-pencil');
        $grid->addButton('add', 'Presenter.Admin.Early.Add.H1', 'Early:add', [$this->eventEntity->getId()])
            ->setIcon('fa fa-plus-circle');
    }
}