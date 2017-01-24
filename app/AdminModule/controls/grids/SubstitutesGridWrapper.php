<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\Entities\EventEntity;
use App\Model\Entities\SubstituteEntity;
use App\Model\Facades\SubstituteFacade;
use Nette\Localization\ITranslator;
use Tracy\Debugger;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class SubstitutesGridWrapper extends GridWrapper {

    /** @var  SubstituteFacade */
    private $substituteFacade;

    /** @var  EventEntity */
    private $event;

    public function __construct(ITranslator $translator, SubstituteFacade $substituteFacade) {
        parent::__construct($translator);
        $this->substituteFacade = $substituteFacade;
    }

    /**
     * @param EventEntity $entity
     * @return $this
     */
    public function setEvent(EventEntity $event) {
        $this->event = $event;
        return $this;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->substituteFacade->getAllSubstitutesGridModel($this->event));
    }

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendOrderColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendActions(Grid $grid) {
        $grid->addActionEvent('detail', 'Přijmout', function (...$args) {
            Debugger::barDump($args);
        })
            ->setIcon('fa fa-eye');
    }

    protected function appendOrderColumns(Grid $grid) {
        $grid->addColumnText('state', 'Stav')
            ->setSortable()
            ->setReplacement([
                SubstituteEntity::STATE_WAITING => 'Čekající',
                SubstituteEntity::STATE_ACTIVE => 'Přijatý'
            ])
            ->setFilterSelect([
                NULL => '',
                SubstituteEntity::STATE_WAITING => 'Čekající',
                SubstituteEntity::STATE_ACTIVE => 'Přijatý'
            ]);
        $grid->addColumnText('firstName', 'Jméno')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('lastName', 'Příjmení')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnEmail('email', 'Email')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnDate('created', 'Vytvořeno', 'd.m.Y H:i:s')
            ->setDefaultSort('ASC')
            ->setFilterDateRange();
        $grid->addColumnText('count', 'Přihlášek')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnText('early.lastName', 'Přednostní')
            ->setCustomRender(function (SubstituteEntity $susbstitute) {
                $early = $susbstitute->getEarly();
                if (!$early) {
                    return '';
                }
                return $early->getFullName();
            });
    }
}