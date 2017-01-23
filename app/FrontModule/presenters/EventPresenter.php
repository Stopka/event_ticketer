<?php

namespace App\FrontModule\Presenters;

use App\CompanyModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\CompanyModule\Controls\Forms\SubstituteFormWrapper;
use App\Controls\Forms\IOrderFormWrapperFactory;
use App\Controls\Forms\OrderFormWrapper;
use App\Model;


class EventPresenter extends BasePresenter {

    /**
     * @var Model\Facades\EventFacade
     * @inject
     */
    public $eventFacade;

    /**
     * @var Model\Facades\ApplicationFacade
     * @inject
     */
    public $applicationFacade;

    /**
     * @var Model\Facades\OptionFacade
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
        $event = $this->eventFacade->getPublicAvailibleEvent($id);
        if(!$event){
            $this->flashMessage('Událost nebyla nalezena','warning');
            $this->redirect('Homepage:');
        }
        if ($event->isCapacityFull($this->applicationFacade->countIssuedApplications($event))) {
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
            $this->flashMessage('Událost nebyla nalezena','warning');
            $this->redirect('Homepage:');
        }
        if (!$event->isCapacityFull($this->applicationFacade->countIssuedApplications($event))) {
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
        $event = $this->eventFacade->getEvent($id);
        if(!$event){
            $this->flashMessage('Událost nebyla nalezena','warning');
            $this->redirect('Homepage:');
        }
        $this->template->event = $event;
        $this->template->event_issued = $this->applicationFacade->countIssuedApplications($event);
        $this->template->event_reserved = $this->applicationFacade->countReservedApplications($event);
        $this->template->options = $this->optionFacade->getOptionsWithLimitedCapacity($event);
    }

    /**
     * @param Model\Entities\OptionEntity $option
     * @return integer
     */
    public function countOptionsReserved(Model\Entities\OptionEntity $option){
        return $this->applicationFacade->countReservedApplicationsWithOption($option);
    }

}
