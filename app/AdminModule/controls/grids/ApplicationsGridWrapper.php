<?php

namespace App\AdminModule\Controls\Grids;

use App\Model\Entities\ApplicationEntity;
use App\Model\Facades\ApplicationFacade;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;
use Tracy\Debugger;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class ApplicationsGridWrapper extends GridWrapper {

    /** @var  ApplicationFacade */
    private $applicationFacade;

    public function __construct(ITranslator $translator, ApplicationFacade $applicationFacade) {
        parent::__construct($translator);
        $this->applicationFacade = $applicationFacade;
    }


    function configure(\App\Grids\Grid $grid) {
        $grid->setModel($this->applicationFacade->getAllApplicationsGridModel());
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
        $grid->addColumnText('order.firstName', 'Jméno rodiče')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('order.lastName', 'Příjmení rodiče')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        /*$grid->addColumnText('order.phone','Telefon rodiče')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnEmail('order.email','Email rodiče')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('address','Adresa')
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
            ->setReplacement([ApplicationEntity::GENDER_MALE => 'Muž', ApplicationEntity::GENDER_FEMALE => 'Žena'])
            ->setFilterSelect([null => '', ApplicationEntity::GENDER_MALE => 'Muž', ApplicationEntity::GENDER_FEMALE => 'Žena']);
        $grid->addColumnDate('birthDate', 'Datum narození')
            ->setSortable()
            ->setFilterDateRange();
        $grid->addColumnText('birthCode', 'Kod rodného čísla')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnDate('order.created', 'Vytvořeno')
            ->setSortable()
            ->setFilterDateRange();
        $grid->addColumnText('deposited', 'Záloha')
            ->setSortable()
            ->setCustomRender(function (ApplicationEntity $application) {
                return Html::el('a', [
                    'href' => $this->link('inverseValue!', 'deposited', $application->getId()),
                    'title' => 'Přepnout'
                ])
                    ->setText($application->isDeposited() ? 'Ano' : 'Ne');
            })
            ->setReplacement([true => 'Ano', false => 'Ne'])
            ->setFilterSelect([null => '', true => 'Ano', false => 'Ne']);
        $grid->addColumnText('payed', 'Doplatek')
            ->setSortable()
            ->setCustomRender(function (ApplicationEntity $application) {
                return Html::el('a', [
                    'href' => $this->link('inverseValue!', 'payed', $application->getId()),
                    'title' => 'Přepnout'
                ])
                    ->setText($application->isPayed() ? 'Ano' : 'Ne');
            })
            ->setReplacement([true => 'Ano', false => 'Ne'])
            ->setFilterSelect([null => '', true => 'Ano', false => 'Ne']);
        $grid->addColumnText('signed', 'Přihláška')
            ->setCustomRender(function (ApplicationEntity $application) {
                return Html::el('a', [
                    'href' => $this->link('inverseValue!', 'signed', $application->getId()),
                    'title' => 'Přepnout'
                ])
                    ->setText($application->isSigned() ? 'Ano' : 'Ne');
            })
            ->setSortable()
            ->setReplacement([true => 'Ano', false => 'Ne'])
            ->setFilterSelect([null => '', true => 'Ano', false => 'Ne']);
        $grid->addColumnText('invoiced', 'Faktura')
            ->setCustomRender(function (ApplicationEntity $application) {
                return Html::el('a', [
                    'href' => $this->link('inverseValue!', 'invoiced', $application->getId()),
                    'title' => 'Přepnout'
                ])
                    ->setText($application->isInvoiced() ? 'Ano' : 'Ne');
            })
            ->setSortable()
            ->setReplacement([true => 'Ano', false => 'Ne'])
            ->setFilterSelect([null => '', true => 'Ano', false => 'Ne']);
        $grid->addActionEvent('detail', 'Detail', function (...$args) {
            Debugger::barDump($args);
        })
            ->setIcon('fa fa-eye');
    }

    public function handleInverseValue($key, $applicationId) {
        $this->applicationFacade->inverseValue($key,$applicationId);
        $this->redirect('this');
    }
}