<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Grids\GridWrapperDependencies;
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

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendEarlyColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyColumns(Grid $grid) {
        $grid->addColumnText('firstName', 'Jméno')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('lastName', 'Příjmení')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('email', 'Email')
            ->setSortable()
            ->setFilterText();
    }

    protected function appendEarlyWaveColumns(Grid $grid) {
        $grid->addColumnText('earlyWave.name', 'Vlna')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('earlyWave.startDate', 'Začátek')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('earlyWave.inviteSent', 'Odesláno')
            ->setReplacement([true=>'Ano',false=>'Ne'])
            ->setSortable()
            ->setFilterSelect([null=>'', true=>'Ano',false=>'Ne']);
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Upravit', 'Early:edit')
            ->setIcon('fa fa-pencil-o');
    }
}