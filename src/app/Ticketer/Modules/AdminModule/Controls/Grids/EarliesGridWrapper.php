<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Daos\EarlyDao;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;

class EarliesGridWrapper extends GridWrapper
{

    /** @var  EarlyDao */
    private EarlyDao $earlyDao;

    /** @var  EventEntity */
    private EventEntity $eventEntity;

    /**
     * EarliesGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param EarlyDao $earlyDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, EarlyDao $earlyDao)
    {
        parent::__construct($gridWrapperDependencies);
        $this->earlyDao = $earlyDao;
    }

    /**
     * @param EventEntity $eventEntity
     */
    public function setEventEntity(EventEntity $eventEntity): void
    {
        $this->eventEntity = $eventEntity;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->earlyDao->getEventEarliesGridModel($this->eventEntity));
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendEarlyColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyColumns(Grid $grid): void
    {
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('lastName', 'Attribute.Person.LastName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('email', 'Attribute.Person.Email')
            ->setSortable()
            ->setFilterText();
    }

    protected function appendEarlyWaveColumns(Grid $grid): void
    {
        $grid->addColumnText('earlyWave.name', 'Entity.Singular.EarlyWave')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('earlyWave.startDate', 'Attribute.Event.StartDate')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('earlyWave.inviteSent', 'Attribute.Event.InviteSent')
            ->setReplacement([true => 'Value.Boolean.Yes', false => 'Value.Boolean.No'])
            ->setSortable()
            ->setFilterSelect([null => '', true => 'Value.Boolean.Yes', false => 'Value.Boolean.No']);
    }


    protected function appendActions(Grid $grid): void
    {
        $grid->addAction('edit', 'Form.Action.Edit', 'Early:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionCallback(
            'link',
            'Attribute.Link',
            function (string $uid): void {
                $this->getPresenter()->redirect(':Front:Early:default', [$uid]);
            }
        )
            ->setIcon('fa fa-link')
            ->setRenderCondition(
                function (EarlyEntity $earlyEntity): bool {
                    return $earlyEntity->isReadyToRegister();
                }
            );
        $grid->addToolbarButton('Early:add', 'Presenter.Admin.Early.Add.H1', [$this->eventEntity->getId()])
            ->setIcon('fa fa-plus-circle');
    }
}
