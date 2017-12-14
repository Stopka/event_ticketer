<?php

namespace App\AdminModule\Controls\Grids;

use App\Controls\Grids\Grid;
use App\Controls\Grids\GridWrapperDependencies;
use App\Model\Persistence\Attribute\IGender;
use App\Model\Persistence\Dao\ApplicationDao;
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

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  ChoiceManager */
    private $choiceManager;

    /** @var  EventEntity */
    private $event;

    /** @var int  */
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

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->applicationDao->getEventApplicationsGridModel($this->event));
    }

    protected function configure(Grid $grid) {
        $this->loadModel($grid);
        $this->appendCartColumns($grid);
        $this->appendApplicationColumns($grid);
        $this->appendAdditionsColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendActions(Grid $grid) {
        $grid->addActionEvent('detail', 'Detail', function ($id) {
            $this->getPresenter()->redirect('Cart:default', $id);
        })
            ->setIcon('fa fa-eye')
            ->setPrimaryKey('cart.id');
        $grid->addActionEvent('upravit', 'Upravit', function ($id) {
            $this->getPresenter()->redirect('Cart:edit', $id);
        })
            ->setPrimaryKey('cart.id')
            ->setIcon('fa fa-pencil');
    }

    protected function appendApplicationColumns(Grid $grid) {
        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setDefaultSort('ASC')
            ->setFilterNumber();
        $grid->addColumnText('state', 'Stav')
            ->setSortable()
            ->setReplacement([
                ApplicationEntity::STATE_WAITING => 'Nové',
                ApplicationEntity::STATE_RESERVED => 'Rezervováno',
                ApplicationEntity::STATE_FULFILLED => 'Doplaceno',
                ApplicationEntity::STATE_CANCELLED => 'Zrušeno'
            ])
            ->setSortable()
            ->setFilterSelect([
                NULL => '',
                ApplicationEntity::STATE_WAITING => 'Nové',
                ApplicationEntity::STATE_RESERVED => 'Rezervováno',
                ApplicationEntity::STATE_FULFILLED => 'Doplaceno',
                ApplicationEntity::STATE_CANCELLED => 'Zrušeno'
            ]);

        /*$grid->addColumnText('address','Adresa')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('city','Město')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('zip','PSČ')
            ->setFilterText()
            ->setSuggestion();*/
        $grid->addColumnText('firstName', 'Jméno')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('lastName', 'Příjmení')
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
        $grid->addColumnText('birthCode', 'Kod rodného čísla')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('cart.created', 'Vytvořeno', 'd.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();
        /*$grid->addColumnText('invoiced', 'Faktura')
            ->setCustomRender(function (ApplicationEntity $application) {
                return $isPayedLink = Html::el('a', ['title' => 'Přepnout','href' => $this->link('inverseValue!', 'invoiced', $application->getId()),])
                    ->addHtml(Html::el('i',['class'=>'fa '.($application->isInvoiced()?'fa-check-square-o':'fa-square-o')]));;
            })
            ->setSortable()
            ->setReplacement([true => 'Ano', false => 'Ne'])
            ->setFilterSelect([null => '', true => 'Ano', false => 'Ne']);*/
    }

    protected function appendCartColumns(Grid $grid) {
        $grid->addColumnText('cart.firstName', 'Jméno rodiče')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('cart.lastName', 'Příjmení rodiče')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        /*
        $grid->addColumnText('cart.phone','Telefon rodiče')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnEmail('cart.email','Email rodiče')
            ->setFilterText()
            ->setSuggestion();
        */
    }

    protected function getCounterNumber():int{
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
                        $isPayedLink = Html::el('a', [
                            'id' => 'choice_' . $choice->getId(),
                            'class' => 'ajax',
                            'data-ajax-off' => 'unique',
                            'title' => 'Přepnout',
                            'href' => $this->link('inverseChoicePayed!#choice_' . $choice->getId(), $choice->getId()),])
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

    public function handleInverseChoicePayed($choiceId) {
        $this->choiceManager->inverseChoicePayed($choiceId);
        if ($this->getPresenter()->isAjax()) {
            $this->redrawControl();
            return;
        }
        $this->redirect('this');
    }
}