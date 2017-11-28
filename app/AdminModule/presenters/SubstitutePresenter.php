<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\ISubstitutesGridWrapperFactory;
use App\AdminModule\Controls\Grids\SubstitutesGridWrapper;
use App\Model\Persistence\Dao\EventDao;

class SubstitutePresenter extends BasePresenter {

    /** @var  ISubstitutesGridWrapperFactory */
    public $substitutesGridWrapperFactory;

    /** @var EventDao */
    public $eventDao;

    /**
     * SubstitutePresenter constructor.
     * @param ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory
     * @param EventDao $additionDao
     */
    public function __construct(ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory, EventDao $additionDao) {
        parent::__construct();
        $this->substitutesGridWrapperFactory = $substitutesGridWrapperFactory;
        $this->eventDao = $additionDao;
    }


    public function actionDefault($id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashMessage("Událost nenalezena!","error");
            $this->redirect('Homepage:');
        }
        /** @var SubstitutesGridWrapper $substitutesGridWrapper */
        $substitutesGridWrapper = $this->getComponent('substitutesGrid');
        $substitutesGridWrapper->setEvent($event);
        $this->template->event = $event;
    }

    protected function createComponentSubstitutesGrid(){
        return $this->substitutesGridWrapperFactory->create();
    }
}
