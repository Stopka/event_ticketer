<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\EarlyWaveDao;
use App\Model\Persistence\Entity\EventEntity;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class EarlyWavesGridWrapper extends GridWrapper {

    /** @var  EarlyWaveDao */
    private $earlyWaveDao;

    /** @var  EventEntity */
    private $eventEntity;

    public function __construct(GridWrapperDependencies $gridWrapperDependencies, EarlyWaveDao $earlyWaveDao) {
        parent::__construct($gridWrapperDependencies);
        $this->earlyWaveDao = $earlyWaveDao;
    }

    /**
     * @param EventEntity $eventEntity
     */
    public function setEventEntity(EventEntity $eventEntity): void {
         $this->eventEntity = $eventEntity;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->earlyWaveDao->getEventEarlyWavesGridModel($this->eventEntity));
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyWaveColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Event.EarlyWave')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('startDate', 'Entity.Event.StartDate')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('inviteSent', 'Entity.Event.InviteSent')
            ->setReplacement([true=>'Entity.Boolean.Yes',false=>'Entity.Boolean.No'])
            ->setSortable()
            ->setFilterSelect([null=>'', true=>'Entity.Boolean.Yes',false=>'Entity.Boolean.No']);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Form.Action.Edit', 'Early:edit')
            ->setIcon('fa fa-pencil');
    }
}