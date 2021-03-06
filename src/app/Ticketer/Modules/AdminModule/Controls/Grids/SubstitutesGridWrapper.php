<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Daos\SubstituteDao;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\Enums\SubstituteStateEnum;
use Ticketer\Model\Database\Managers\SubstituteManager;
use Nette\Utils\Html;
use Ticketer\Model\Dtos\Uuid;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class SubstitutesGridWrapper extends GridWrapper
{

    private SubstituteDao $substituteDao;

    private SubstituteManager $substituteManager;

    private EventEntity $event;

    /**
     * SubstitutesGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param SubstituteDao $substituteDao
     * @param SubstituteManager $substituteManager
     */
    public function __construct(
        GridWrapperDependencies $gridWrapperDependencies,
        SubstituteDao $substituteDao,
        SubstituteManager $substituteManager
    ) {
        parent::__construct($gridWrapperDependencies);
        $this->substituteDao = $substituteDao;
        $this->substituteManager = $substituteManager;
    }

    /**
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent(EventEntity $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->substituteDao->getAllSubstitutesGridModel($this->event));
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendSubstituteColumns($grid);
        $this->appendEarlyControls($grid);
        $this->appendActions($grid);
    }

    protected function appendSubstituteColumns(Grid $grid): void
    {
        $grid->addColumnText('state', 'Attribute.State')
            ->setSortable()
            ->setReplacement(
                [
                    SubstituteStateEnum::WAITING => 'Value.Substitute.State.Waiting',
                    SubstituteStateEnum::ACTIVE => 'Value.Substitute.State.Active',
                    SubstituteStateEnum::OVERDUE => 'Value.Substitute.State.Overdue',
                    SubstituteStateEnum::ORDERED => 'Value.Substitute.State.Ordered',
                ]
            )
            ->setFilterSelect(
                [
                    null => '',
                    SubstituteStateEnum::WAITING => 'Value.Substitute.State.Waiting',
                    SubstituteStateEnum::ACTIVE => 'Value.Substitute.State.Active',
                    SubstituteStateEnum::OVERDUE => 'Value.Substitute.State.Overdue',
                    SubstituteStateEnum::ORDERED => 'Value.Substitute.State.Ordered',
                ]
            );
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('lastName', 'Attribute.Person.LastName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('email', 'Attribute.Person.Email')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDateTime('created', 'Attribute.Created')
            ->setSortable()
            ->setSort('ASC')
            ->setFilterDate();
        $grid->addColumnNumber('count', 'Attribute.Application.Count')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDateTime('endDate', 'Attribute.Event.EndDate')
            ->setFilterDate();
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function appendEarlyControls(Grid $grid): void
    {
        $grid->addColumnText('early', 'Entity.Singular.Early')
            ->setRenderer(
                function (SubstituteEntity $susbstitute): Html {
                    $html = Html::el();
                    $early = $susbstitute->getEarly();
                    if (null !== $early) {
                        $html = Html::el('div');
                        $html->addHtml(
                            Html::el('div', ['class' => 'early-fullName fillName'])
                                ->addText($early->getFullName())
                        );
                        $html->addHtml(
                            Html::el('div', ['class' => 'early-email email'])
                                ->addText($early->getEmail() ?? '')
                        );
                    }

                    return $html;
                }
            );
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function appendActions(Grid $grid): void
    {
        $grid->addActionCallback('activate', 'Form.Action.Activate', [$this, 'onActivate'])
            ->setRenderCondition(
                function (SubstituteEntity $substitute): bool {
                    return $substitute->getState()->isActivable();
                }
            )
            ->setIcon('check-square');
        $grid->addActionCallback(
            'link',
            'Attribute.Link',
            function (string $uid): void {
                $this->getPresenter()->redirect(':Front:Substitute:default', [$uid]);
            }
        )
            ->setIcon('link')
            ->setRenderCondition(
                function (SubstituteEntity $substituteEntity): bool {
                    return $substituteEntity->isActive() && !$substituteEntity->isOrdered();
                }
            );
    }

    /**
     * @param string $substituteId
     * @throws \Exception
     */
    public function onActivate(string $substituteId): void
    {
        $substituteUuid = Uuid::fromString($substituteId);
        $substitute = $this->substituteDao->getSubstitute($substituteUuid);
        if (null === $substitute) {
            return;
        }
        $this->substituteManager->activateSubstitute($substitute);
        $this->redirect('this');
    }
}
