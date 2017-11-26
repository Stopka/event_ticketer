<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\Persistence\Dao\SubstituteDao;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\Manager\SubstituteManager;
use Nette\Localization\ITranslator;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class SubstitutesGridWrapper extends GridWrapper {

    /** @var  SubstituteDao */
    private $substituteDao;

    /** @var  SubstituteManager */
    private $substituteManager;

    /** @var  EventEntity */
    private $event;

    public function __construct(ITranslator $translator, SubstituteDao $substituteDao, SubstituteManager $substituteManager) {
        parent::__construct($translator);
        $this->substituteDao = $substituteDao;
        $this->substituteManager = $substituteManager;
    }

    /**
     * @param \App\Model\Persistence\Entity\EventEntity $entity
     * @return $this
     */
    public function setEvent(EventEntity $event) {
        $this->event = $event;
        return $this;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->substituteDao->getAllSubstitutesGridModel($this->event));
    }

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendOrderColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendOrderColumns(Grid $grid) {
        $grid->addColumnText('state', 'Stav')
            ->setSortable()
            ->setReplacement([
                SubstituteEntity::STATE_WAITING => 'Ve frontě',
                SubstituteEntity::STATE_ACTIVE => 'Přijatý',
                SubstituteEntity::STATE_OVERDUE => 'Prošlý',
                SubstituteEntity::STATE_ORDERED => 'Registrovaný'
            ])
            ->setFilterSelect([
                NULL => '',
                SubstituteEntity::STATE_WAITING => 'Ve frontě',
                SubstituteEntity::STATE_ACTIVE => 'Přijatý',
                SubstituteEntity::STATE_OVERDUE => 'Prošlý',
                SubstituteEntity::STATE_ORDERED => 'Registrovaný'
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


    protected function appendActions(Grid $grid) {
        $grid->addActionEvent('activate', 'Přijmout',[$this,'onActivate'])
            ->setDisable(function(SubstituteEntity $substitute){
                return $substitute->isOrdered()||$substitute->isActive();
            })
            ->setIcon('fa fa-check-square');
    }

    public function onActivate($substituteId){
        $this->substituteManager->activateSubstitute($substituteId);
    }
}