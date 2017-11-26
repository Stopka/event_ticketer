<?php

namespace App\AdminModule\Controls\Grids;

use App\Grids\Grid;
use App\Model\Persistence\Attribute\IGender;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\ChoiceManager;
use Nette\Localization\ITranslator;
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

    /** @var  \App\Model\Persistence\Entity\EventEntity */
    private $event;

    public function __construct(ITranslator $translator, ApplicationDao $applicationDao, ChoiceManager $choiceManager) {
        parent::__construct($translator);
        $this->applicationDao = $applicationDao;
        $this->choiceManager = $choiceManager;
    }

    /**
     * @param EventEntity $entity
     * @return $this
     */
    public function setEvent(EventEntity $event) {
        $this->event = $event;
        return $this;
    }

    protected function loadModel(Grid $grid) {
        $grid->setModel($this->applicationDao->getAllApplicationsGridModel($this->event));
    }

    protected function configure(\App\Grids\Grid $grid) {
        $this->loadModel($grid);
        $this->appendOrderColumns($grid);
        $this->appendApplicationColumns($grid);
        $this->appendAdditionsColumns($grid);
        $this->appendActions($grid);
    }

    protected function appendActions(Grid $grid) {
        $grid->addActionEvent('detail', 'Detail', function ($id) {
            return $this->getPresenter()->redirect('Order:default',$id);
        })
            ->setIcon('fa fa-eye')
            ->setPrimaryKey('order.id');
        $grid->addActionEvent('upravit', 'Upravit', function ($id) {
            return $this->getPresenter()->redirect('Order:edit',$id);
        })
            ->setPrimaryKey('order.id')
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
        $grid->addColumnDate('order.created', 'Vytvořeno','d.m.Y H:i:s')
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

    protected function appendOrderColumns(Grid $grid) {
        $grid->addColumnText('order.firstName', 'Jméno rodiče')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('order.lastName', 'Příjmení rodiče')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        /*
        $grid->addColumnText('order.phone','Telefon rodiče')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnEmail('order.email','Email rodiče')
            ->setFilterText()
            ->setSuggestion();
        */
    }

    protected function appendAdditionsColumns(Grid $grid) {
        foreach ($this->event->getAdditions() as $addition) {
            $grid->addColumnText('addition' . $addition->getId(), $addition->getName())
                ->setCustomRender(function (ApplicationEntity $application) use ($addition) {
                    $result = Html::el();
                    foreach ($application->getChoices() as $choice){
                        if($choice->getOption()->getAddition()->getId()!=$addition->getId()){
                            continue;
                        }
                        $isPayedLink = Html::el('a', [
                            'id'=>'choice_'.$choice->getId(),
                            'class'=>'ajax',
                            'data-ajax-off'=>'unique',
                            'title' => 'Přepnout',
                            'href' => $this->link('inverseChoicePayed!#choice_'.$choice->getId(), $choice->getId()),])
                            ->addHtml(Html::el('i',['class'=>'fa '.($choice->isPayed()?'fa-check-square-o':'fa-square-o')]));
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
        if($this->getPresenter()->isAjax()){
            $this->redrawControl();
            return;
        }
        $this->redirect('this');
    }
}