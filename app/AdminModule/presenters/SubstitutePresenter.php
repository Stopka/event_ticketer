<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\ISubstitutesGridWrapperFactory;
use App\AdminModule\Controls\Grids\SubstitutesGridWrapper;
use App\Model\Facades\EventFacade;

class SubstitutePresenter extends BasePresenter {

    /**
     * @var  ISubstitutesGridWrapperFactory
     * @inject
     */
    public $substitutesGridWrapperFactory;

    /**
     * @var EventFacade
     * @inject
     */
    public $eventFacade;

    public function actionDefault($id) {
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
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
