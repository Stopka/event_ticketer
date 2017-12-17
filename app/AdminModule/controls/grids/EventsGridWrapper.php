<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class EventsGridWrapper extends GridWrapper {

    /** @var  EventDao */
    private $eventDao;

    /**
     * EventsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param EventDao $additionDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, EventDao $additionDao) {
        parent::__construct($gridWrapperDependencies);
        $this->eventDao = $additionDao;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->eventDao->getAllEventsGridModel());
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendEventColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEventColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('state', 'Entity.Event.State.State')
            ->setSortable()
            ->setReplacement([
                EventEntity::STATE_INACTIVE => 'Entity.Event.State.Inactive',
                EventEntity::STATE_ACTIVE => 'Entity.Event.State.Active',
                EventEntity::STATE_CLOSED => 'Entity.Event.State.Closed',
                EventEntity::STATE_CANCELLED => 'Entity.Event.State.Cancelled'
            ])
            ->setFilterSelect([
                NULL => '',
                EventEntity::STATE_INACTIVE => 'Neaktivní',
                EventEntity::STATE_ACTIVE => 'Aktivní',
                EventEntity::STATE_CLOSED => 'Dokončený',
                EventEntity::STATE_CANCELLED => 'Zrušený'
            ]);
        $grid->addColumnNumber('capacity', 'Entity.Event.Capacity', '0')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnDate('startDate', 'Entity.Event.Public')
            ->setDefaultSort('ASC')
            ->setFilterDateRange();
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Form.Action.Edit','Event:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionHref('applications','Entity.Event.Applications','Application:')
            ->setIcon('fa fa-ticket');
    }
}