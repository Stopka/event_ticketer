<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\SubstituteDao;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\Manager\SubstituteManager;

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

    /**
     * SubstitutesGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param SubstituteDao $substituteDao
     * @param SubstituteManager $substituteManager
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, SubstituteDao $substituteDao, SubstituteManager $substituteManager) {
        parent::__construct($gridWrapperDependencies);
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

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendCartColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendCartColumns(Grid $grid) {
        $grid->addColumnText('state', 'Entity.Substitute.State.State')
            ->setSortable()
            ->setReplacement([
                SubstituteEntity::STATE_WAITING => 'Entity.Substitute.State.Waiting',
                SubstituteEntity::STATE_ACTIVE => 'Entity.Substitute.State.Active',
                SubstituteEntity::STATE_OVERDUE => 'Entity.Substitute.State.Overdue',
                SubstituteEntity::STATE_ORDERED => 'Entity.Substitute.State.Ordered'
            ])
            ->setFilterSelect([
                NULL => '',
                SubstituteEntity::STATE_WAITING => 'Entity.Substitute.State.Waiting',
                SubstituteEntity::STATE_ACTIVE => 'Entity.Substitute.State.Active',
                SubstituteEntity::STATE_OVERDUE => 'Entity.Substitute.State.Overdue',
                SubstituteEntity::STATE_ORDERED => 'Entity.Substitute.State.Ordered'
            ]);
        $grid->addColumnText('firstName', 'Entity.Person.FirstName')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('lastName', 'Entity.Person.LastName')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnEmail('email', 'Entity.Person.Email')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnDate('created', 'Entity.Created', 'd.m.Y H:i:s')
            ->setDefaultSort('ASC')
            ->setFilterDateRange();
        $grid->addColumnText('count', 'Entity.Application.Count')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnText('early.lastName', 'Entity.Person.Type.Substitute')
            ->setCustomRender(function (SubstituteEntity $susbstitute) {
                $early = $susbstitute->getEarly();
                if (!$early) {
                    return '';
                }
                return $early->getFullName();
            });
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionEvent('activate', 'Form.Action.Activate',[$this,'onActivate'])
            ->setDisable(function(SubstituteEntity $substitute){
                return $substitute->isOrdered()||$substitute->isActive();
            })
            ->setIcon('fa fa-check-square');
    }

    public function onActivate($substituteId){
        $this->substituteManager->activateSubstitute($substituteId);
    }
}