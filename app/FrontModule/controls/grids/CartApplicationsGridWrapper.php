<?php

namespace App\FrontModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\ChoiceDao;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\CartEntity;
use Grido\Components\Filters\Filter;
use Nette\Utils\Html;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class CartApplicationsGridWrapper extends GridWrapper {

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
    public function __construct(GridWrapperDependencies $gridWrapperDependencies, ApplicationDao $applicationDao, ChoiceDao $choiceDao) {
        parent::__construct($gridWrapperDependencies);
        $this->applicationDao = $applicationDao;
        $this->choiceDao = $choiceDao;
    }

    /**
     * @param \App\Model\Persistence\Entity\CartEntity $entity
     * @return $this
     */
    public function setCart(CartEntity $cart) {
        $this->cart = $cart;
        return $this;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->applicationDao->getCartApplicationsGridModel($this->cart));
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $grid->getTablePrototype()->addClass('grido-no-paginator grido-no-filter grido-no-reset');
        $this->appendApplicationColumns($grid);
        $this->appendAdditionsColumns($grid);
        $this->appendActions($grid);
        $grid->setFilterRenderType(Filter::RENDER_INNER);
        $grid->setDefaultPerPage(200);
        $grid->setPerPageList([200]);
    }

    protected function appendActions(Grid $grid) {
        /*$grid->addActionEvent('detail', '', function (...$args) {
            Debugger::barDump($args);
        });*/
    }

    protected function appendApplicationColumns(Grid $grid) {
        $grid->addColumnNumber('id', 'ID')
            ->setDefaultSort('ASC');
        $grid->addColumnText('state', 'Stav')
            ->setReplacement([
                ApplicationEntity::STATE_WAITING => 'Nové',
                ApplicationEntity::STATE_RESERVED => 'Rezervováno',
                ApplicationEntity::STATE_FULFILLED => 'Doplaceno',
                ApplicationEntity::STATE_CANCELLED => 'Zrušeno'
            ]);

        $grid->addColumnText('street', 'Ulice');
        $grid->addColumnText('address', 'Číslo popisné');
        $grid->addColumnText('city', 'Město');
        $grid->addColumnText('zip', 'PSČ');
        $grid->addColumnText('firstName', 'Jméno');
        $grid->addColumnText('lastName', 'Příjmení');
        $grid->addColumnDate('cart.created', 'Vytvořeno');
    }

    protected function appendAdditionsColumns(Grid $grid) {
        foreach ($this->cart->getEvent()->getAdditions() as $addition) {
            if ($addition->isHidden()) {
                continue;
            }
            $grid->addColumnText('addition' . $addition->getId(), $addition->getName())
                ->setCustomRender(function (ApplicationEntity $application) use ($addition) {
                    $result = Html::el();
                    foreach ($application->getChoices() as $choice) {
                        if ($choice->getOption()->getAddition()->getId() != $addition->getId()) {
                            continue;
                        }
                        $isPayedLink = Html::el('strong')
                            ->addHtml(Html::el('i', ['class' => 'fa ' . ($choice->isPayed() ? 'fa-check-square-o' : 'fa-square-o')]));
                        $name = Html::el('span')->setText($choice->getOption()->getName());
                        $result->addHtml(
                            Html::el('div')
                                ->addHtml($isPayedLink)
                                ->addHtml($name)
                        );
                    }
                    return $result;
                });
        }
    }
}