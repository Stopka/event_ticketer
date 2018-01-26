<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\SubstituteDao;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\Manager\SubstituteManager;
use Nette\Utils\Html;

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
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent(EventEntity $event) {
        $this->event = $event;
        return $this;
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function loadModel(Grid $grid) {
        $grid->setModel($this->substituteDao->getAllSubstitutesGridModel($this->event));
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendSubstituteColumns($grid);
        $this->appendEarlyControls($grid);
        $this->appendActions($grid);
    }

    protected function appendSubstituteColumns(Grid $grid) {
        $grid->addColumnText('state', 'Attribute.State')
            ->setSortable()
            ->setReplacement([
                SubstituteEntity::STATE_WAITING => 'Value.Substitute.State.Waiting',
                SubstituteEntity::STATE_ACTIVE => 'Value.Substitute.State.Active',
                SubstituteEntity::STATE_OVERDUE => 'Value.Substitute.State.Overdue',
                SubstituteEntity::STATE_ORDERED => 'Value.Substitute.State.Ordered'
            ])
            ->setFilterSelect([
                NULL => '',
                SubstituteEntity::STATE_WAITING => 'Value.Substitute.State.Waiting',
                SubstituteEntity::STATE_ACTIVE => 'Value.Substitute.State.Active',
                SubstituteEntity::STATE_OVERDUE => 'Value.Substitute.State.Overdue',
                SubstituteEntity::STATE_ORDERED => 'Value.Substitute.State.Ordered'
            ]);
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('lastName', 'Attribute.Person.LastName')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnEmail('email', 'Attribute.Person.Email')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnDateTime('created', 'Attribute.Created')
            ->setDefaultSort('ASC')
            ->setFilterDateRange();
        $grid->addColumnText('count', 'Attribute.Application.Count')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnDateTime('endDate', 'Attribute.Event.EndDate')
            ->setFilterDateRange();
    }

    protected function appendEarlyControls(Grid $grid) {
        $grid->addColumnText('early', 'Entity.Singular.Early')
            ->setCustomRender(function (SubstituteEntity $susbstitute) {
                $html = Html::el();
                if ($early = $susbstitute->getEarly()) {
                    $html = Html::el('div');
                    $html->addHtml(Html::el('div', ['class' => 'early-fullName fillName'])->addText($early->getFullName()));
                    $html->addHtml(Html::el('div', ['class' => 'early-email email'])->addText($early->getEmail()));
                }
                return $html;
            });
    }

    protected function appendActions(Grid $grid) {
        $grid->addActionEvent('activate', 'Form.Action.Activate', [$this, 'onActivate'])
            ->setDisable(function (SubstituteEntity $substitute) {
                return !in_array($substitute->getState(), SubstituteEntity::getActivableStates());
            })
            ->setIcon('fa fa-check-square');
        $grid->addActionEvent('link', 'Attribute.Link', function (string $uid) {
            $this->getPresenter()->redirect(':Front:Substitute:default', [$uid]);
        })
            ->setPrimaryKey('uid')
            ->setIcon('fa fa-link')
            ->setDisable(function (SubstituteEntity $substituteEntity) {
                return !$substituteEntity->isActive() || $substituteEntity->isOrdered();
            });
    }

    /**
     * @param $substituteId
     * @throws \Exception
     */
    public function onActivate($substituteId) {
        $substitute = $this->substituteDao->getSubstitute($substituteId);
        if (!$substitute) {
            return;
        }
        $this->substituteManager->activateSubstitute($substitute);
        $this->redirect('this');
    }
}