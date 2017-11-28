<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;
use Nette\Localization\ITranslator;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class EventsGridWrapper extends GridWrapper {

    /** @var  EventDao */
    private $eventDao;

    public function __construct(ITranslator $translator, EventDao $eventDao) {
        parent::__construct($translator);
        $this->eventDao = $eventDao;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->eventDao->getAllEventsGridModel());
    }

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendEventColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEventColumns(Grid $grid) {
        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('state', 'Stav')
            ->setSortable()
            ->setReplacement([
                EventEntity::STATE_INACTIVE => 'Neaktivní',
                EventEntity::STATE_ACTIVE => 'Aktivní',
                EventEntity::STATE_CLOSED => 'Dokončený',
                EventEntity::STATE_CANCELLED => 'Zrušený'
            ])
            ->setFilterSelect([
                NULL => '',
                EventEntity::STATE_INACTIVE => 'Neaktivní',
                EventEntity::STATE_ACTIVE => 'Aktivní',
                EventEntity::STATE_CLOSED => 'Dokončený',
                EventEntity::STATE_CANCELLED => 'Zrušený'
            ]);
        $grid->addColumnNumber('capacity', 'Kapacita', '0')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnDate('startDate', 'Veřejný výdej', 'd.m.Y H:i:s')
            ->setDefaultSort('ASC')
            ->setFilterDateRange();
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit','Upravit','Event:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionHref('applications','Přihlášky','Application:')
            ->setIcon('fa fa-ticket');
    }
}