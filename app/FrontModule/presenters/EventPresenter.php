<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\FrontModule\Controls\Forms\SubstituteFormWrapper;
use App\FrontModule\Controls\IOccupancyControlFactory;
use App\FrontModule\Controls\OccupancyControl;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Dao\OptionDao;


class EventPresenter extends BasePresenter {

    /** @var EventDao */
    public $eventDao;

    /** @var IOrderFormWrapperFactory */
    public $orderFormWrapperFactory;

    /** @var ISubstituteFormWrapperFactory */
    public $substituteFormWrapperFactory;

    /** @var  IOccupancyControlFactory */
    public $occupancyControlFactory;

    /**
     * EventPresenter constructor.
     * @param EventDao $eventDao
     * @param ApplicationDao $applicationDao
     * @param OptionDao $optionDao
     * @param IOrderFormWrapperFactory $orderFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param IOccupancyControlFactory $occupancyControlFactory
     */
    public function __construct(EventDao $eventDao, IOrderFormWrapperFactory $orderFormWrapperFactory, ISubstituteFormWrapperFactory $substituteFormWrapperFactory, IOccupancyControlFactory $occupancyControlFactory) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->orderFormWrapperFactory = $orderFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
        $this->occupancyControlFactory = $occupancyControlFactory;
    }


    public function actionDefault($id = null){
        $this->redirect('register',$id);
    }

    public function actionRegister($id = null) {
        $event = $this->eventDao->getEvent($id);
        if(!$event||!$event->isActive()){
            $this->flashMessage('Událost nebyla nalezena','error');
            $this->redirect('Homepage:');
        }
        if(!$event->isStarted()){
            $this->flashMessage('Událost ještě nebyla zpřístupněna veřejnosti.', 'warning');
            $this->redirect('Homepage:');
        }
        if ($event->isCapacityFull()) {
            $this->flashMessage('Již nejsou žádné volné přihlášky.', 'warning');
            $this->redirect('substitute', $id);
        }
        /** @var OrderFormWrapper $orderForm */
        $orderForm = $this->getComponent('orderForm');
        $orderForm->setEvent($event);
        $this->template->event = $event;
    }

    public function actionSubstitute($id = null) {
        $event = $this->eventDao->getPublicAvailibleEvent($id);
        if(!$event){
            $this->flashMessage('Událost nebyla nalezena','error');
            $this->redirect('Homepage:');
        }
        if (!$event->isCapacityFull()) {
            $this->redirect('register', $id);
        }
        /** @var SubstituteFormWrapper $substituteFormWrapper */
        $substituteFormWrapper = $this->getComponent('substituteForm');
        $substituteFormWrapper->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @return OrderFormWrapper
     */
    protected function createComponentOrderForm(){
        return $this->orderFormWrapperFactory->create();
    }

    /**
     * @return SubstituteFormWrapper
     */
    protected function createComponentSubstituteForm() {
        return $this->substituteFormWrapperFactory->create();
    }

    /**
     * @return \App\FrontModule\Controls\OccupancyControl
     */
    protected function createComponentOccupancy(){
        return $this->occupancyControlFactory->create();
    }

    public function renderOccupancy($id = null){
        if(!$id){
            $events = $this->eventDao->getPublicAvailibleEvents();
            if($events){
                $this->redirect('this',$events[0]->getId());
                return;
            }
            $events = $this->eventDao->getPublicFutureEvents();
            if($events){
                $this->redirect('this',$events[0]->getId());
                return;
            }
        }
        $event = $this->eventDao->getEvent($id);
        $this->template->event = $event;
        if($event){
            /** @var OccupancyControl $occupancy */
            $occupancy = $this->getComponent('occupancy');
            $occupancy->setEvent($event);
        }
    }

}
