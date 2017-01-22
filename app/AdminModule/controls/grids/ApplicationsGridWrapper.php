<?php

namespace App\AdminModule\Controls\Grids;

use App\Model\Entities\ApplicationEntity;
use App\Model\Facades\ApplicationFacade;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
class ApplicationsGridWrapper extends GridWrapper {

    /** @var  ApplicationFacade */
    private $applicationFacade;

    public function __construct(ApplicationFacade $applicationFacade) {
        parent::__construct();
        $this->applicationFacade = $applicationFacade;
    }


    function configure(\App\Grids\Grid $grid) {
        $grid->setModel($this->applicationFacade->getAllApplicationsGridModel());
        $grid->addColumnNumber('id','ID')
            ->setSortable()
            ->setFilterNumber();
        $grid->addColumnText('order.firstName','Jméno rodiče')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('order.lastName','Příjmení rodiče')
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('order.phone','Telefon rodiče')
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
            ->setSuggestion();
        $grid->addColumnText('firstName','Jméno')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('lastName','Příjmení')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();
        $grid->addColumnText('gender','Pohlaví')
            ->setReplacement([ApplicationEntity::GENDER_MALE=>'Muž',ApplicationEntity::GENDER_FEMALE=>'Žena'])
            ->setFilterSelect([ApplicationEntity::GENDER_MALE=>'Muž',ApplicationEntity::GENDER_FEMALE=>'Žena']);
        $grid->addColumnDate('birthDate','Datum narození')
            ->setSortable()
            ->setFilterDateRange();
        $grid->addColumnText('birthCode','Kod r. č.')
            ->setFilterText();
    }
}