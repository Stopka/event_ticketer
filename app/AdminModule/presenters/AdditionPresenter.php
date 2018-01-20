<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\AdditionFormWrapper;
use App\AdminModule\Controls\Forms\IAdditionFormWrapperFactory;
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

    /** @var IAdditionFormWrapperFactory  */
    private $additionFormWrapperFactory;

    public function __construct(EventDao $eventDao, AdditionDao $additionDao, IAdditionsGridWrapperFactory $additionsGridWrapperFactory, IAdditionFormWrapperFactory $additionFormWrapperFactory) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->additionDao = $additionDao;
        $this->additionsGridWrapperFactory = $additionsGridWrapperFactory;
        $this->additionFormWrapperFactory = $additionFormWrapperFactory;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->redirect("Event:edit");
        }
        /** @var AdditionsGridWrapper $additionsGrid */
        $additionsGrid = $this->getComponent('additionsGrid');
        $additionsGrid->setEventEntity($event);
        $this->template->event = $event;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionAdd(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashTranslatedMessage('Error.Event.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        /** @var AdditionFormWrapper $additionForm */
        $additionForm = $this->getComponent('additionForm');
        $additionForm->setEventEntity($event);
        $this->template->addition = null;
        $this->template->event = $event;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit(int $id) {
        $addition = $this->additionDao->getAddition($id);
        if(!$addition){
            $this->flashTranslatedMessage('Error.Addition.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        /** @var AdditionFormWrapper $additionForm */
        $additionForm = $this->getComponent('additionForm');
        $additionForm->setAdditionEntity($addition);
        $this->template->addition = $addition;
        $this->template->event = $addition->getEvent();
    }

    /**
     * @return AdditionsGridWrapper
     */
    public function createComponentAdditionsGrid(){
        return $this->additionsGridWrapperFactory->create();
    }

    /**
     * @return AdditionFormWrapper
     */
    public function createComponentAdditionForm(){
        return $this->additionFormWrapperFactory->create();
    }
}
