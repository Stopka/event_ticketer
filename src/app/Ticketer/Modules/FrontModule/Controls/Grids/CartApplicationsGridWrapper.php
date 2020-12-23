<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls\Grids;

use Ticketer\Controls\Grids\Grid;
use Ticketer\Controls\Grids\GridWrapperDependencies;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\ChoiceDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\CartEntity;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class CartApplicationsGridWrapper extends GridWrapper
{

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  ChoiceDao */
    private $choiceDao;

    /** @var  CartEntity */
    private $cart;

    /**
     * CartApplicationsGridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     * @param ApplicationDao $applicationDao
     * @param ChoiceDao $choiceDao
     */
    public function __construct(
        GridWrapperDependencies $gridWrapperDependencies,
        ApplicationDao $applicationDao,
        ChoiceDao $choiceDao
    ) {
        parent::__construct($gridWrapperDependencies);
        $this->applicationDao = $applicationDao;
        $this->choiceDao = $choiceDao;
    }

    /**
     * @param CartEntity $cart
     * @return CartApplicationsGridWrapper
     */
    public function setCart(CartEntity $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @param Grid $grid
     */
    protected function loadModel(Grid $grid): void
    {
        $grid->setDataSource($this->applicationDao->getCartApplicationsGridModel($this->cart));
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function configure(Grid $grid): void
    {
        $this->loadModel($grid);
        $this->appendApplicationColumns($grid);
        $this->appendAdditionsColumns($grid);
        $this->appendActions($grid);
        $grid->setDefaultPerPage(200);
        $grid->setItemsPerPageList([200]);
    }

    protected function appendActions(Grid $grid): void
    {
        /*$grid->addActionEvent('detail', '', function (...$args) {
            Debugger::barDump($args);
        });*/
    }

    protected function appendApplicationColumns(Grid $grid): void
    {
        $grid->addColumnNumber('id', 'Attribute.Id')
            ->setSort('ASC');
        $grid->addColumnText('state', 'Attribute.State')
            ->setReplacement(
                [
                    ApplicationEntity::STATE_WAITING => 'Value.Application.State.Waiting',
                    ApplicationEntity::STATE_OCCUPIED => 'Value.Application.State.Occupied',
                    ApplicationEntity::STATE_FULFILLED => 'Value.Application.State.Fulfilled',
                    ApplicationEntity::STATE_CANCELLED => 'Value.Application.State.Cancelled',
                ]
            );
        $grid->addColumnText('address', 'Adresa');
        $grid->addColumnText('city', 'Město');
        $grid->addColumnText('zip', 'PSČ');
        $grid->addColumnText('firstName', 'Attribute.Person.FirstName');
        $grid->addColumnText('lastName', 'Attribute.Person.LastName');
        $grid->addColumnDateTime('created', 'Attribute.Created');
    }

    protected function isVisible(AdditionEntity $additionEntity): bool
    {
        return $additionEntity->isVisibleIn(AdditionEntity::VISIBLE_CUSTOMER);
    }

    /**
     * @param Grid $grid
     * @throws DataGridException
     */
    protected function appendAdditionsColumns(Grid $grid): void
    {
        $event = $this->cart->getEvent();
        if (null === $event) {
            return;
        }
        foreach ($event->getAdditions() as $option) {
            if (!$this->isVisible($option)) {
                continue;
            }
            $grid->addColumnText('addition' . $option->getId(), (string)$option->getName())
                ->setRenderer(
                    function (ApplicationEntity $application) use ($option): Html {
                        $result = Html::el();
                        foreach ($application->getChoices() as $choice) {
                            $option = $choice->getOption();
                            if (null === $option) {
                                continue;
                            }
                            $addition = $option->getAddition();
                            if (null === $addition) {
                                continue;
                            }
                            if ($addition->getId() !== $option->getId()) {
                                continue;
                            }
                            $isPayedLink = Html::el(
                                'i',
                                [
                                    'class' => 'fa ' . ($choice->isPayed() ? 'fa-check-square-o' : 'fa-square-o'),
                                ]
                            );
                            $name = Html::el('span')->setText((string)$option->getName());
                            $result->addHtml(
                                Html::el('div', ['class' => 'addition-link'])
                                    ->addHtml($isPayedLink)
                                    ->addText(' ')
                                    ->addHtml($name)
                            );
                        }

                        return $result;
                    }
                );
        }
    }
}
