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

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->earlyDao->getEventEarliesGridModel($this->eventEntity));
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendEarlyColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyColumns(Grid $grid) {
        $grid->addColumnText('firstName', 'Entity.Person.FirstName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('lastName', 'Entity.Person.LastName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('email', 'Entity.Person.Email')
            ->setSortable()
            ->setFilterText();
    }

    protected function appendEarlyWaveColumns(Grid $grid) {
        $grid->addColumnText('earlyWave.name', 'Entity.Event.EarlyWave')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('earlyWave.startDate', 'Entity.Event.StartDate')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('earlyWave.inviteSent', 'Entity.Event.InviteSent')
            ->setReplacement([true=>'Entity.Boolean.Yes',false=>'Entity.Boolean.No'])
            ->setSortable()
            ->setFilterSelect([null=>'', true=>'Entity.Boolean.Yes',false=>'Entity.Boolean.No']);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Form.Action.Edit', 'Early:edit')
            ->setIcon('fa fa-pencil-o');
    }
}