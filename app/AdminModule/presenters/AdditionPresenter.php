<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\AdditionsGridWrapper;
use App\AdminModule\Controls\Grids\IAdditionsGridWrapperFactory;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\EventDao;

class AdditionPresenter extends BasePresenter {

    /** @var  EventDao */
    private $eventDao;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var  IAdditionsGridWrapperFactory */
    private $additionsGridWrapperFactory;

    private $additionFormWrapperFactory;

    public function __construct(EventDao $eventDao, AdditionDao $additionDao, IAdditionsGridWrapperFactory $additionsGridWrapperFactory) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->additionDao = $additionDao;
        $this->additionsGridWrapperFactory = $additionsGridWrapperFactory;
    }

    public function actionDefault($id = null) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->redirect("Event:edit");
        }
        /** @var AdditionsGridWrapper $additionsGrid */
        $additionsGrid = $this->getComponent('additionsGrid');
        $additionsGrid->setEventEntity($event);
        $this->template->event = $event;
    }

    public function actionAdd(){
        $this->redirect("edit");
    }

    public function actionEdit($id = null){
        $addition = $this->additionDao->getAddition($id);
        if($id&&!$addition){
            $this->redirect("this", [null]);
        }
        if($addition){

        }
        $this->template->addition = $addition;
        $this->template->event = $addition->getEvent();
    }

    /**
     * @return AdditionsGridWrapper
     */
    public function createComponentAdditionsGrid(){
        return $this->additionsGridWrapperFactory->create();
    }
}
