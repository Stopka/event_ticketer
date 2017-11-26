<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\FrontModule\Controls\Forms\SubstituteFormWrapper;
use App\Model;


class EventPresenter extends BasePresenter {

    /**
     * @var \App\Model\Persistence\Dao\EventDao
     * @inject
     */
    public $eventFacade;

    /**
     * @var \App\Model\Persistence\Dao\ApplicationDao
     * @inject
     */
    public $applicationFacade;

    /**
     * @var \App\Model\Persistence\Dao\OptionDao
     * @inject
     */
    public $optionFacade;

    /**
     * @var IOrderFormWrapperFactory
     * @inject
     */
    public $orderFormWrapperFactory;

    /**
     * @var ISubstituteFormWrapperFactory
     * @inject
     */
    public $substituteFormWrapperFactory;

    public function actionDefault($id = null){
        $this->redirect('register',$id);
    }

    public function actionRegister($id = null) {
        $event = $this->eventFacade->getEvent($id);
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
        $event = $this->eventFacade->getPublicAvailibleEvent($id);
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

    public function renderOccupancy($id = null){
        if(!$id){
            $events = $this->eventFacade->getPublicAvailibleEvents();
            if($events){
                $this->redirect('this',$events[0]->getId());
                return;
            }
            $events = $this->eventFacade->getPublicFutureEvents();
            if($events){
                $this->redirect('this',$events[0]->getId());
                return;
            }
        }
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
            $this->flashMessage('Událost nebyla nalezena','error');
            $this->redirect('Homepage:');
        }
        $this->template->event = $event;
        $this->template->event_issued = $this->applicationFacade->countIssuedApplications($event);
        $this->template->event_reserved = $this->applicationFacade->countReservedApplications($event);
        $this->template->options = $this->optionFacade->getOptionsWithLimitedCapacity($event);
    }

    /**
     * @param \App\Model\Persistence\Entity\OptionEntity $option
     * @return integer
     */
    public function countOptionsReserved(Model\Persistence\Entity\OptionEntity $option){
        return $this->applicationFacade->countReservedApplicationsWithOption($option);
    }

}
