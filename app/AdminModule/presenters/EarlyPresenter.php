<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\AdditionFormWrapper;
use App\AdminModule\Controls\Grids\EarliesGridWrapper;
use App\AdminModule\Controls\Grids\IEarliesGridWrapperFactory;
use App\Model\Persistence\Dao\EventDao;

class EarlyPresenter extends BasePresenter {

    /** @var  EventDao */
    private $eventDao;

    /** @var  IEarliesGridWrapperFactory */
    private $earliesGridWrapperFactory;

    public function __construct(EventDao $eventDao, IEarliesGridWrapperFactory $earliesGridWrapperFactory) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->earliesGridWrapperFactory = $earliesGridWrapperFactory;
    }

    public function actionDefault($id = null) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->redirect("Event:edit");
        }
        /** @var EarliesGridWrapper $earliesGrid */
        $earliesGrid = $this->getComponent('earliesGrid');
        $earliesGrid->setEventEntity($event);
        $this->template->event = $event;
    }

    public function actionAdd($id){
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashMessage('Událost nenalezena','error');
            $this->redirect("Homepage:");
        }
        /** @var AdditionFormWrapper $additionForm */
        $additionForm = $this->getComponent('additionForm');
        $additionForm->setEventEntity($event);
        $this->template->addition = null;
        $this->template->event = $event;
    }

    public function actionEdit($id = null){
        $addition = $this->additionDao->getAddition($id);
        if(!$addition){
            $this->flashMessage('Přídavek nenalezen','error');
            $this->redirect("Homepage:");
        }
        /** @var AdditionFormWrapper $additionForm */
        $additionForm = $this->getComponent('additionForm');
        $additionForm->setAdditionEntity($addition);
        $this->template->addition = $addition;
        $this->template->event = $addition->getEvent();
    }

    /**
     * @return EarliesGridWrapper
     */
    protected function createComponentEarliesGrid(){
        return $this->earliesGridWrapperFactory->create();
    }
}
