<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Entity\EventEntity;
use Nette\Localization\ITranslator;

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

    public function __construct(ITranslator $translator, AdditionDao $additionDao) {
        parent::__construct($translator);
        $this->additionDao = $additionDao;
    }

    /**
     * @param EventEntity $eventEntity
     */
    public function setEventEntity(EventEntity $eventEntity): void {
         $this->eventEntity = $eventEntity;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->additionDao->getEventAdditions($this->eventEntity));
    }

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendAdditionColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendAdditionColumns(Grid $grid) {
        $grid->addColumnText('name', 'Název')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('minimum', 'Minimum')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnNumber('maximum', 'Maximum')
            ->setSortable()
            ->setFilterNumber();
    }


    protected function appendActions(Grid $grid) {

    }
}