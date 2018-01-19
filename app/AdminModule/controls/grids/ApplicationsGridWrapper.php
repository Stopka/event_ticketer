<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Attribute\IGender;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\IOrder;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\ChoiceManager;
use Nette\Utils\Html;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class ApplicationsGridWrapper extends GridWrapper {

    const OPERATION_DELEGATE = "delegate";

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  ChoiceManager */
    private $choiceManager;

    /** @var  EventEntity */
    private $event;

    /** @var int */
    private $counter = 0;

    /**
     * ApplicationsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param ApplicationDao $applicationDao
     * @param ChoiceManager $choiceManager
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, ApplicationDao $applicationDao, ChoiceManager $choiceManager) {
        parent::__construct($gridWrapperDependencies);
        $this->applicationDao = $applicationDao;
        $this->choiceManager = $choiceManager;
    }

    /**
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent(EventEntity $event): self {
        $this->event = $event;
        return $this;
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function loadModel(Grid $grid) {
        $grid->setModel($this->applicationDao->getEventApplicationsGridModel($this->event));
    }

    /**
     * @param Grid $grid
     * @throws \Grido\Exception
     */
    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendCartColumns($grid);
        $this->appendReservationColumns($grid);
        $this->appendApplicationColumns($grid);
        $this->appendAdditionsColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendActions(Grid $grid) {
        $grid->setOperation([
            self::OPERATION_DELEGATE => "Presenter.Admin.Reservation.Delegate.H1"
        ], function ($operation, $application_ids) {
            array_walk($application_ids, function (string &$id) {
                $id = ApplicationEntity::getIdFromAplhaNumeric($id);
            });
            switch ($operation) {
                case self::OPERATION_DELEGATE:
                    $this->getPresenter()->redirect('Reservation:delegate', [
                        'id' => $this->event->getId(),
                        'ids' => $application_ids
                    ]);
                    break;
            }
        })
            ->setPrimaryKey('idAlphaNumeric');
        $grid->addActionEvent('detailCart', 'Form.Action.Detail', function ($id) {
            $application = $this->applicationDao->getApplication($id);
            if (!$application || !$application->getCart()) {
                return;
            }
            $this->getPresenter()->redirect('Cart:default', $application->getCart()->getId());
        })
            ->setIcon('fa fa-eye')
            ->setDisable(function (ApplicationEntity $applicationEntity) {
                return $applicationEntity->getCart() === null;
            });
        $grid->addActionEvent('editCart', 'Form.Action.Edit', function ($id) {
            $application = $this->applicationDao->getApplication($id);
            if (!$application || !$application->getCart()) {
                return;
            }
            $this->getPresenter()->redirect('Cart:edit', $application->getCart()->getId());
        })
            ->setIcon('fa fa-pencil')
            ->setDisable(function (ApplicationEntity $applicationEntity) {
                return $applicationEntity->getCart() === null;
            });
        $grid->addButton('reserve', 'Presenter.Admin.Application.Reserve.H1', "Application:reserve", ['id' => $this->event->getId()])
            ->setIcon('fa fa-address-book-o');
        $grid->addButton('export', 'Presenter.Admin.Application.Export.H1', "Application:export", ['id' => $this->event->getId()])
            ->setIcon('fa fa-download');
    }

    protected function appendApplicationColumns(Grid $grid) {
        $grid->addColumnNumber('number', 'Attribute.Number')
            ->setSortable()
            ->setDefaultSort(IOrder::ORDER_ASC)
            ->setFilterNumber();
        $grid->addColumnText('state', 'Attribute.State')
            ->setSortable()
            ->setReplacement(ApplicationEntity::getAllStates())
            ->setSortable()
            ->setFilterSelect(array_merge([NULL => ''], ApplicationEntity::getAllStates()));
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('lastName', 'Attribute.Person.LastName')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('gender', 'Pohlaví')
            ->setSortable()
            ->setReplacement([IGender::MALE => 'Muž', IGender::FEMALE => 'Žena'])
            ->setFilterSelect([null => '', IGender::MALE => 'Muž', IGender::FEMALE => 'Žena']);
        $grid->addColumnDate('birthDate', 'Datum narození')
            ->setSortable()
            ->setFilterDateRange();
        $grid->addColumnDateTime('created', 'Attribute.Created')
            ->setSortable()
            ->setFilterDateRange();
    }

    protected function appendCartColumns(Grid $grid) {
        $grid->addColumnText('cart', 'Entity.Singular.Cart')
            ->setCustomRender(function (ApplicationEntity $applicationEntity) {
                $html = Html::el();
                if ($cart = $applicationEntity->getCart()) {
                    $html->addHtml(Html::el('div', ['class' => 'cart-id id'])->addText($cart->getId()));
                    $html->addHtml(Html::el('div', ['class' => 'cart-fullName fillName'])->addText($cart->getFullName()));
                    $html->addHtml(Html::el('div', ['class' => 'cart-email email'])->addText('(' . $cart->getEmail() . ')'));
                }
                return $html;
            });
    }

    protected function appendReservationColumns(Grid $grid) {
        $grid->addColumnText('reservation', 'Entity.Singular.Reservation')
            ->setCustomRender(function (ApplicationEntity $applicationEntity) {
                $html = Html::el();
                if ($reservation = $applicationEntity->getReservation()) {
                    $html = Html::el('a', ['href' => $this->getPresenter()->link(':Front:Reservation:register', $reservation->getId())]);
                    $html->addHtml(Html::el('div', ['class' => 'reservation-fullName fullName'])->addText($reservation->getFullName()));
                    $html->addHtml(Html::el('div', ['class' => 'reservation-email email'])->addText($reservation->getEmail()));
                }
                return $html;
            });
    }

    protected function getCounterNumber(): int {
        return $this->counter++;
    }

    protected function appendAdditionsColumns(Grid $grid) {
        foreach ($this->event->getAdditions() as $addition) {
            $grid->addColumnText('addition' . $this->getCounterNumber(), $addition->getName())
                ->setCustomRender(function (ApplicationEntity $application) use ($addition) {
                    $result = Html::el();
                    foreach ($application->getChoices() as $choice) {
                        if ($choice->getOption()->getAddition()->getId() != $addition->getId()) {
                            continue;
                        }
                        $isPayedIcon = Html::el('i', [
                            'class' => ['fa', ($choice->isPayed() ? 'fa-check-square-o' : 'fa-square-o')]
                        ]);
                        $name = Html::el('span', ['class' => 'addition-name'])
                            ->setText($choice->getOption()->getName());
                        $result->addHtml(
                            Html::el('a', [
                                'class' => ['addition-link', 'ajax'],
                                'id' => 'choice_' . $choice->getId(),
                                'data-ajax-off' => 'unique',
                                'title' => $this->getTranslator()->translate('Form.Action.Switch'),
                                'href' => $this->link('inverseChoicePayed!#choice_' . $choice->getId(), $choice->getId())
                            ])
                                ->addHtml($isPayedIcon)
                                ->addText(' ')
                                ->addHtml($name)
                        );
                    }
                    return $result;
                });
        }
    }

    /**
     * @param $choiceId
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    public function handleInverseChoicePayed($choiceId) {
        $this->choiceManager->inverseChoicePayed($choiceId);
        if ($this->getPresenter()->isAjax()) {
            $this->redrawControl();
            return;
        }
        $this->redirect('this');
    }
}