<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\EarlyFormWrapper;
use App\AdminModule\Controls\Forms\IEarlyFormWrapperFactory;
use App\AdminModule\Controls\Grids\EarliesGridWrapper;
use App\AdminModule\Controls\Grids\IEarliesGridWrapperFactory;
use App\Model\Persistence\Dao\EarlyDao;
use App\Model\Persistence\Dao\EventDao;

class EarlyPresenter extends BasePresenter {

    /** @var  EventDao */
    private $eventDao;

    /** @var  IEarliesGridWrapperFactory */
    private $earliesGridWrapperFactory;

    /** @var IEarlyFormWrapperFactory */
    private $earlyFormWrapperFactory;

    /** @var EarlyDao */
    private $earlyDao;

    public function __construct(EventDao $eventDao, IEarliesGridWrapperFactory $earliesGridWrapperFactory, IEarlyFormWrapperFactory $earlyFormWrapperFactory, EarlyDao $earlyDao) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->earlyDao =  $earlyDao;
        $this->earliesGridWrapperFactory = $earliesGridWrapperFactory;
        $this->earlyFormWrapperFactory = $earlyFormWrapperFactory;
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
        /** @var EarlyFormWrapper $earlyForm */
        $earlyForm = $this->getComponent('earlyForm');
        $earlyForm->setEventEntity($event);
        $this->template->early = null;
        $this->template->event = $event;
    }

    public function actionEdit($id = null){
        $early = $this->earlyDao->getEarly($id);
        if(!$early){
            $this->flashMessage('Přídavek nenalezen','error');
            $this->redirect("Homepage:");
        }
        /** @var EarlyFormWrapper $earlyForm */
        $earlyForm = $this->getComponent('earlyForm');
        $earlyForm->setEarlyEntity($early);
        $this->template->early = $early;
        $this->template->event = $earlyForm->getEventEntity();
    }

    /**
     * @return EarliesGridWrapper
     */
    protected function createComponentEarliesGrid(){
        return $this->earliesGridWrapperFactory->create();
    }

    /**
     * @return EarlyFormWrapper
     */
    protected function createComponentEarlyForm(){
        return $this->earlyFormWrapperFactory->create();
    }
}