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

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function loadModel(Grid $grid) {
        $grid->setModel($this->earlyWaveDao->getEventEarlyWavesGridModel($this->eventEntity));
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyWaveColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Singular.EarlyWave')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('startDate', 'Attribute.Event.StartDate')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('inviteSent', 'Attribute.Event.InviteSent')
            ->setReplacement([true=>'Value.Boolean.Yes',false=>'Value.Boolean.No'])
            ->setSortable()
            ->setFilterSelect([null=>'', true=>'Value.Boolean.Yes',false=>'Value.Boolean.No']);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit', 'Form.Action.Edit', 'EarlyWave:edit')
            ->setIcon('fa fa-pencil');
        $grid->addButton('add', 'Presenter.Admin.EarlyWave.Add.H1', 'EarlyWave:add', [$this->eventEntity->getId()])
            ->setIcon('fa fa-plus-circle');
    }
}