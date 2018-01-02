<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Exception\Exception;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\EventManager;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class EventsGridWrapper extends GridWrapper {

    /** @var  EventDao */
    private $eventDao;

    /** @var EventManager */
    private $eventManager;

    /**
     * EventsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param EventDao $additionDao
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, EventDao $additionDao, EventManager $eventManager) {
        parent::__construct($gridWrapperDependencies);
        $this->eventDao = $additionDao;
        $this->eventManager = $eventManager;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->eventDao->getAllEventsGridModel());
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendEventColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEventColumns(Grid $grid) {
        $grid->addColumnText('name', 'Entity.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('state', 'Entity.Event.State.State')
            ->setSortable()
            ->setReplacement([
                EventEntity::STATE_INACTIVE => 'Entity.Event.State.Inactive',
                EventEntity::STATE_ACTIVE => 'Entity.Event.State.Active',
                EventEntity::STATE_CLOSED => 'Entity.Event.State.Closed',
                EventEntity::STATE_CANCELLED => 'Entity.Event.State.Cancelled'
            ])
            ->setFilterSelect([
                NULL => '',
                EventEntity::STATE_INACTIVE => 'Entity.Event.State.Inactive',
                EventEntity::STATE_ACTIVE => 'Entity.Event.State.Active',
                EventEntity::STATE_CLOSED => 'Entity.Event.State.Closed',
                EventEntity::STATE_CANCELLED => 'Entity.Event.State.Cancelled'
            ]);
        $grid->addColumnNumber('capacity', 'Entity.Event.Capacity', '0')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnDate('startDate', 'Entity.Event.Public')
            ->setDefaultSort('ASC')
            ->setFilterDateRange();
    }


    protected function appendActions(Grid $grid) {
        $grid->addActionHref('edit', 'Form.Action.Edit', 'Event:edit')
            ->setIcon('fa fa-pencil');
        $grid->addActionHref('applications', 'Entity.Event.Applications', 'Application:')
            ->setIcon('fa fa-ticket');
        $grid->addActionEvent('activate', 'Form.Action.Activate', [$this, 'onActivateClicked'])
            ->setIcon('fa fa-toggle-on')
            ->setDisable(function (EventEntity $eventEntity) {
                return $eventEntity->isActive();
            });
        $grid->addActionEvent('deactivate', 'Form.Action.Deactivate', [$this, 'onDeactivateClicked'])
            ->setIcon('fa fa-toggle-off')
            ->setDisable(function (EventEntity $eventEntity) {
                return !$eventEntity->isActive();
            });
        $grid->addActionEvent('cancel', 'Form.Action.Cancel', [$this, 'onCancelClicked'])
            ->setIcon('fa fa-ban')
            ->setDisable(function (EventEntity $eventEntity) {
                return !$eventEntity->isActive();
            })
            ->setConfirm(function (EventEntity $eventEntity) {
                return $this->getTranslator()->translate('Grid.Event.Confirm.Cancel',['event'=>$eventEntity->getName()]);
            });

        $grid->addActionEvent('close', 'Form.Action.Close', [$this, 'onCloseClicked'])
            ->setIcon('fa fa-times-circle')
            ->setDisable(function (EventEntity $eventEntity) {
                return !$eventEntity->isActive();
            })
            ->setConfirm(function (EventEntity $eventEntity) {
                return $this->getTranslator()->translate('Grid.Event.Confirm.Close',['event'=>$eventEntity->getName()]);
            });
    }

    public function onActivateClicked(string $id) {
        try {
            $event = $this->eventDao->getEvent($id);
            $this->eventManager->setEventState($event, EventEntity::STATE_ACTIVE);
            $this->flashTranslatedMessage("Grid.Event.Message.Activate.Success");
        } catch (Exception $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    public function onDeactivateClicked(string $id) {
        try {
            $event = $this->eventDao->getEvent($id);
            $this->eventManager->setEventState($event, EventEntity::STATE_INACTIVE);
            $this->flashTranslatedMessage("Grid.Event.Message.Dectivate.Success");
        } catch (Exception $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    public function onCancelClicked(string $id) {
        try {
            $event = $this->eventDao->getEvent($id);
            $this->eventManager->setEventState($event, EventEntity::STATE_CANCELLED);
            $this->flashTranslatedMessage("Grid.Event.Message.Cancel.Success");
        } catch (Exception $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    public function onCloseClicked(string $id) {
        try {
            $event = $this->eventDao->getEvent($id);
            $this->eventManager->setEventState($event, EventEntity::STATE_CLOSED);
            $this->flashTranslatedMessage("Grid.Event.Message.Close.Success");
        } catch (Exception $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }
}