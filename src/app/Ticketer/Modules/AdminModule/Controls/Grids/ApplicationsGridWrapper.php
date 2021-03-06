<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Nette\Application\AbortException;
use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Exceptions\TranslatedException;
use Ticketer\Model\Database\Enums\GenderEnum;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\OrderEnum;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\ApplicationManager;
use Ticketer\Model\Database\Managers\ChoiceManager;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class ApplicationsGridWrapper extends GridWrapper
{

    public const OPERATION_DELEGATE = "delegate";

    private ApplicationDao $applicationDao;

    private ChoiceManager $choiceManager;

    private EventEntity $event;

    private int $counter = 0;

    private ApplicationManager $applicationManager;

    /**
     * ApplicationsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param ApplicationDao $applicationDao
     * @param ChoiceManager $choiceManager
     * @param ApplicationManager $applicationManager
     */
    public function __construct(
        GridWrapperDependencies $gridWrapperDependencies,
        ApplicationDao $applicationDao,
        ChoiceManager $choiceManager,
        ApplicationManager $applicationManager
    ) {
        parent::__construct($gridWrapperDependencies);
        $this->applicationDao = $applicationDao;
        $this->choiceManager = $choiceManager;
        $this->applicationManager = $applicationManager;
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
        $grid->setDataSource($this->applicationDao->getEventApplicationsGridModel($this->event));
    }

    /**
     * @param Grid $grid
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendCartColumns($grid);
        $this->appendReservationColumns($grid);
        $this->appendApplicationColumns($grid);
        $this->appendAdditionsColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendActions(Grid $grid): void
    {
        $action = $grid->addGroupButtonAction('Presenter.Admin.Reservation.Delegate.H1');
        $action->onClick[] = function (array $applicationIds): void {
            $this->getPresenter()->redirect(
                'Reservation:delegate',
                [
                    'id' => $this->event->getId()->toString(),
                    'ids' => $applicationIds,
                ]
            );
        };
        /*$grid->addActionCallback(
            'detailCart',
            'Form.Action.Detail',
            function (string $id): void {
                $uuid = Uuid::fromString($id);
                $application = $this->applicationDao->getApplication($uuid);
                if (null === $application || null === $application->getCart()) {
                    return;
                }
                $this->getPresenter()->redirect('Cart:default', $application->getCart()->getId()->toString());
            }
        )
            ->setIcon('eye')
            ->setRenderCondition(
                function (ApplicationEntity $applicationEntity): bool {
                    return null !== $applicationEntity->getCart();
                }
            );*/
        $grid->addActionCallback(
            'editCart',
            'Form.Action.Edit',
            function (string $id): void {
                $uuid = Uuid::fromString($id);
                $application = $this->applicationDao->getApplication($uuid);
                if (null === $application) {
                    return;
                }
                $cart = $application->getCart();
                if (null === $cart) {
                    return;
                }
                $this->getPresenter()->redirect('Cart:edit', $cart->getId()->toString());
            }
        )
            ->setIcon('pencil')
            ->setRenderCondition(
                function (ApplicationEntity $applicationEntity): bool {
                    return null !== $applicationEntity->getCart();
                }
            );
        $grid->addActionCallback(
            'editReservation',
            'Form.Action.Edit',
            function ($id): void {
                $application = $this->applicationDao->getApplication($id);
                if (null === $application) {
                    return;
                }
                $this->getPresenter()->redirect(
                    'Application:editReservation',
                    [
                        'id' => $this->event->getId()->toString(),
                        'ids' => [$application->getId()->toString()],
                    ]
                );
            }
        )
            ->setIcon('pencil')
            ->setRenderCondition(
                function (ApplicationEntity $applicationEntity): bool {
                    return $applicationEntity->getState()->isReserved()
                        && null !== $applicationEntity->getCart();
                }
            );
        $grid->addActionCallback('cancel', 'Form.Action.Cancel', [$this, 'onCancelClicked'])
            ->setIcon('ban')
            ->setConfirmation(
                new CallbackConfirmation(
                    function (ApplicationEntity $applicationEntity): string {
                        return $this->getTranslator()->translate(
                            'Grid.Application.Confirm.Cancel',
                            ['application' => '#' . $applicationEntity->getId()]
                        );
                    }
                )
            )
            ->setRenderCondition(
                function (ApplicationEntity $applicationEntity): bool {
                    return $applicationEntity->getState()->isIssued();
                }
            );
        $grid->addAction('pdf', 'Entity.Singular.Application', 'Application:pdf')
            ->setIcon('ticket');
        $grid->addToolbarButton(
            "Application:reserve",
            'Presenter.Admin.Application.Reserve.H1',
            ['id' => $this->event->getId()->toString()]
        )
            ->setIcon('address-book-o');
        $grid->addToolbarButton(
            "Application:export",
            'Presenter.Admin.Application.Export.H1',
            ['id' => $this->event->getId()->toString()]
        )
            ->setIcon('download');
    }

    protected function appendApplicationColumns(Grid $grid): void
    {
        $grid->addColumnNumber('number', 'Attribute.Number')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('state', 'Attribute.State')
            ->setSortable()
            ->setReplacement(ApplicationStateEnum::getLabels())
            ->setSortable()
            ->setFilterSelect(array_merge([null => ''], ApplicationStateEnum::getLabels()));
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('lastName', 'Attribute.Person.LastName')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('gender', 'Pohlaví')
            ->setSortable()
            ->setReplacement(
                [
                    GenderEnum::MALE()->getValue() => 'Muž',
                    GenderEnum::FEMALE()->getValue() => 'Žena',
                ]
            )
            ->setFilterSelect(
                [
                    null => '',
                    GenderEnum::MALE()->getValue() => 'Muž',
                    GenderEnum::FEMALE()->getValue() => 'Žena',
                ]
            );
        $grid->addColumnDate('birthDate', 'Datum narození')
            ->setSortable()
            ->setFilterDate();
        $grid->addColumnDateTime('created', 'Attribute.Created')
            ->setSortable()
            ->setSort(OrderEnum::ASC()->getValue())
            ->setFilterDate();
    }

    protected function appendCartColumns(Grid $grid): void
    {
        $grid->addColumnText('cart', 'Entity.Singular.Cart')
            ->setRenderer(
                function (ApplicationEntity $applicationEntity): Html {
                    $html = Html::el();
                    $cart = $applicationEntity->getCart();
                    if (null !== $cart) {
                        $html = Html::el(
                            'a',
                            [
                                'href' => $this->getPresenter()->link(
                                    'Cart:default',
                                    $cart->getId()->toString()
                                ),
                            ]
                        );
                        $html->addHtml(
                            Html::el('div', ['class' => 'cart-id id'])
                                ->addText('#')
                                ->addText((string)$cart->getNumber())
                        );
                        $html->addHtml(
                            Html::el('div', ['class' => 'cart-fullName fillName'])
                                ->addText($cart->getFullName())
                        );
                        $html->addHtml(
                            Html::el('div', ['class' => 'cart-email email'])
                                ->addText($cart->getEmail() ?? '')
                        );
                    }

                    return $html;
                }
            );
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function onCancelClicked(string $id): void
    {
        $uuid = Uuid::fromString($id);
        try {
            $application = $this->applicationDao->getApplication($uuid);
            if (null === $application) {
                return;
            }
            $this->applicationManager->cancelApplication($application);
            $this->flashTranslatedMessage("Grid.Application.Message.Cancel.Success");
        } catch (TranslatedException $e) {
            $this->flashMessage($e->getTranslatedMessage($this->getTranslator()));
        }
        $this->redirect('this');
    }

    protected function appendReservationColumns(Grid $grid): void
    {
        $grid->addColumnText('reservation', 'Entity.Singular.Reservation')
            ->setRenderer(
                function (ApplicationEntity $applicationEntity): Html {
                    $html = Html::el();
                    $reservation = $applicationEntity->getReservation();
                    if (null !== $reservation) {
                        $html = Html::el();
                        if ($reservation->isRegisterReady()) {
                            $html = Html::el(
                                'a',
                                [
                                    'href' => $this->getPresenter()->link(
                                        ':Front:Reservation:register',
                                        $reservation->getId()->toString()
                                    ),
                                ]
                            );
                        }
                        $html->addHtml(
                            Html::el('div', ['class' => 'reservation-id id'])->addText(
                                '#' . $reservation->getId()->toString()
                            )
                        );
                        $html->addHtml(
                            Html::el('div', ['class' => 'reservation-fullName fullName'])->addText(
                                $reservation->getFullName()
                            )
                        );
                        $html->addHtml(
                            Html::el('div', ['class' => 'reservation-email email'])
                                ->addText($reservation->getEmail() ?? '')
                        );
                    }

                    return $html;
                }
            );
    }

    protected function getCounterNumber(): int
    {
        return $this->counter++;
    }

    protected function appendAdditionsColumns(Grid $grid): void
    {
        foreach ($this->event->getAdditions() as $addition) {
            if (!$addition->getVisibility()->isPreview()) {
                continue;
            }
            $grid->addColumnText('addition' . $this->getCounterNumber(), (string)$addition->getName())
                ->setRenderer(
                    function (ApplicationEntity $application) use ($addition): Html {
                        $result = Html::el();
                        foreach ($application->getChoices() as $choice) {
                            $choiceOption = $choice->getOption();
                            if (null === $choiceOption) {
                                continue;
                            }
                            $choiceAddition = $choiceOption->getAddition();
                            if (null === $choiceAddition || $choiceAddition->getId() !== $addition->getId()) {
                                continue;
                            }
                            $isPayedIcon = Html::el(
                                'i',
                                [
                                    'class' => ['fa', ($choice->isPayed() ? 'fa-check-square-o' : 'fa-square-o')],
                                ]
                            );
                            $name = Html::el('span', ['class' => 'addition-name'])
                                ->setText((string)$choiceOption->getName());
                            $result->addHtml(
                                Html::el(
                                    'a',
                                    [
                                        'class' => ['addition-link', 'ajax'],
                                        'id' => 'choice_' . $choice->getId(),
                                        'data-ajax-off' => 'unique',
                                        'title' => $this->getTranslator()->translate('Form.Action.Switch'),
                                        'href' => $this->link(
                                            'inverseChoicePayed!#choice_' . $choice->getId(),
                                            $choice->getId()->toString()
                                        ),
                                    ]
                                )
                                    ->addHtml($isPayedIcon)
                                    ->addText(' ')
                                    ->addHtml($name)
                            );
                        }

                        return $result;
                    }
                );
        }
    }

    /**
     * @param string $choiceId
     * @throws AbortException
     * @throws \Exception
     */
    public function handleInverseChoicePayed(string $choiceId): void
    {
        $choiceUuid = Uuid::fromString($choiceId);
        $this->choiceManager->inverseChoicePayed($choiceUuid);
        if ($this->getPresenter()->isAjax()) {
            $this->redrawControl();

            return;
        }
        $this->redirect('this');
    }
}
