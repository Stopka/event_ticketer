<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Daos\EarlyWaveDao;
use Ticketer\Model\Database\Entities\EventEntity;

class EarlyWavesGridWrapper extends GridWrapper
{
    private EarlyWaveDao $earlyWaveDao;

    private EventEntity $eventEntity;

    public function __construct(GridWrapperDependencies $gridWrapperDependencies, EarlyWaveDao $earlyWaveDao)
    {
        parent::__construct($gridWrapperDependencies);
        $this->earlyWaveDao = $earlyWaveDao;
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
        $grid->setDataSource($this->earlyWaveDao->getEventEarlyWavesGridModel($this->eventEntity));
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendEarlyWaveColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEarlyWaveColumns(Grid $grid): void
    {
        $grid->addColumnText('name', 'Entity.Singular.EarlyWave')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('startDate', 'Attribute.Event.StartDate')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnText('inviteSent', 'Attribute.Event.InviteSent')
            ->setReplacement([true => 'Value.Boolean.Yes', false => 'Value.Boolean.No'])
            ->setSortable()
            ->setFilterSelect([null => '', true => 'Value.Boolean.Yes', false => 'Value.Boolean.No']);
    }


    protected function appendActions(Grid $grid): void
    {
        $grid->addAction(
            'edit',
            'Form.Action.Edit',
            'EarlyWave:edit'
        )
            ->setIcon('pencil');
        $grid->addToolbarButton(
            'EarlyWave:add',
            'Presenter.Admin.EarlyWave.Add.H1',
            [$this->eventEntity->getId()->toString()]
        )
            ->setIcon('plus-circle');
    }
}
