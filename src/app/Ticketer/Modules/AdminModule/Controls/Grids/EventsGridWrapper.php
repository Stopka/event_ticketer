<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Nette\Application\AbortException;
use Nette\Utils\Html;
use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Enums\EventStateEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Exceptions\TranslatedException;
use Ticketer\Model\OccupancyIcons;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\EventManager;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class EventsGridWrapper extends GridWrapper
{
    private EventDao $eventDao;

    private EventManager $eventManager;

    private OccupancyIcons $occupancyIcons;

    /**
     * EventsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param EventDao $additionDao
     * @param EventManager $eventManager
     * @param OccupancyIcons $occupancyIcons
     */
    public function __construct(
        GridWrapperDependencies $gridWrapperDependencies,
        EventDao $additionDao,
        EventManager $eventManager,
        OccupancyIcons $occupancyIcons
    ) {
        parent::__construct($gridWrapperDependencies);
        $this->eventDao = $additionDao;
        $this->eventManager = $eventManager;
        $this->occupancyIcons = $occupancyIcons;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->eventDao->getAllEventsGridModel());
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendEventColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendEventColumns(Grid $grid): void
    {
        $grid->addColumnText('name', 'Attribute.Name')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('state', 'Attribute.State')
            ->setSortable()
            ->setReplacement(
                [
                    EventStateEnum::INACTIVE => 'Value.Event.State.Inactive',
                    EventStateEnum::ACTIVE => 'Value.Event.State.Active',
                    EventStateEnum::CLOSED => 'Value.Event.State.Closed',
                    EventStateEnum::CANCELLED => 'Value.Event.State.Cancelled',
                ]
            )
            ->setFilterSelect(
                [
                    null => '',
                    EventStateEnum::INACTIVE => 'Value.Event.State.Inactive',
                    EventStateEnum::ACTIVE => 'Value.Event.State.Active',
                    EventStateEnum::CLOSED => 'Value.Event.State.Closed',
                    EventStateEnum::CANCELLED => 'Value.Event.State.Cancelled',
                ]
            );
        $grid->addColumnNumber('capacity', 'Attribute.Event.Capacity')
            ->setFormat(0)
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('occupnacyIcon', 'Attribute.Event.OccupancyIcon')
            ->setSortable()
            ->setRenderer(
                function (EventEntity $eventEntity): Html {
                    $icon = $eventEntity->getOccupancyIcon();
                    if (null === $icon) {
                        return Html::el();
                    }

                    return $this->occupancyIcons->getLabel($icon);
                }
            );
        $grid->addColumnDate('startDate', 'Attribute.Event.Public')
            ->setSortable()
            ->setSort('ASC')
            ->setFilterDate();
    }


    protected function appendActions(Grid $grid): void
    {
        $grid->addAction('edit', 'Form.Action.Edit', 'Event:edit')
            ->setIcon('pencil');
        $grid->addAction('applications', 'Entity.Plural.Application', 'Application:')
            ->setIcon('ticket');
        $grid->addActionCallback('activate', 'Form.Action.Activate', [$this, 'onActivateClicked'])
            ->setIcon('toggle-on')
            ->setRenderCondition(
                function (EventEntity $eventEntity): bool {
                    return !$eventEntity->isActive();
                }
            );
        $grid->addActionCallback('deactivate', 'Form.Action.Deactivate', [$this, 'onDeactivateClicked'])
            ->setIcon('toggle-off')
            ->setRenderCondition(
                function (EventEntity $eventEntity): bool {
                    return $eventEntity->isActive();
                }
            );
        $grid->addActionCallback('cancel', 'Form.Action.Cancel', [$this, 'onCancelClicked'])
            ->setIcon('ban')
            ->setRenderCondition(
                function (EventEntity $eventEntity): bool {
                    return $eventEntity->isActive();
                }
            )
            ->setConfirmation(
                new CallbackConfirmation(
                    function (EventEntity $eventEntity): string {
                        return $this->getTranslator()->translate(
                            'Grid.Event.Confirm.Cancel',
                            ['event' => $eventEntity->getName()]
                        );
                    }
                )
            );

        $grid->addActionCallback('close', 'Form.Action.Close', [$this, 'onCloseClicked'])
            ->setIcon('times-circle')
            ->setRenderCondition(
                function (EventEntity $eventEntity): bool {
                    return $eventEntity->isActive();
                }
            )
            ->setConfirmation(
                new CallbackConfirmation(
                    function (EventEntity $eventEntity): string {
                        return $this->getTranslator()->translate(
                            'Grid.Event.Confirm.Close',
                            ['event' => $eventEntity->getName()]
                        );
                    }
                )
            );
        $grid->addToolbarButton('Event:add', 'Presenter.Admin.Event.Add.H1')
            ->setIcon('plus-circle');
        $grid->addToolbarButton('Currency:default', 'Presenter.AdminLayout.Setting.H1')
            ->setIcon('wrench');
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function onActivateClicked(string $id): void
    {
        $uuid = Uuid::fromString($id);
        try {
            $event = $this->eventDao->getEvent($uuid);
            $this->eventManager->setEventState($event, EventStateEnum::ACTIVE());
            $this->flashTranslatedMessage("Grid.Event.Message.Activate.Success");
        } catch (TranslatedException $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function onDeactivateClicked(string $id): void
    {
        $uuid = Uuid::fromString($id);
        try {
            $event = $this->eventDao->getEvent($uuid);
            $this->eventManager->setEventState($event, EventStateEnum::INACTIVE());
            $this->flashTranslatedMessage("Grid.Event.Message.Dectivate.Success");
        } catch (TranslatedException $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function onCancelClicked(string $id): void
    {
        $uuid = Uuid::fromString($id);
        try {
            $event = $this->eventDao->getEvent($uuid);
            $this->eventManager->setEventState($event, EventStateEnum::CANCELLED());
            $this->flashTranslatedMessage("Grid.Event.Message.Cancel.Success");
        } catch (TranslatedException $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function onCloseClicked(string $id): void
    {
        $uuid = Uuid::fromString($id);
        try {
            $event = $this->eventDao->getEvent($uuid);
            $this->eventManager->setEventState($event, EventStateEnum::CLOSED());
            $this->flashTranslatedMessage("Grid.Event.Message.Close.Success");
        } catch (TranslatedException $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }
}
