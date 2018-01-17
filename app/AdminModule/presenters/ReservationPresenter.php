<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\DelegateReservationFormWrapper;
use App\AdminModule\Controls\Forms\IDelegateReservationFormWrapperFactory;
use App\Model\Persistence\Dao\ApplicationDao;

class ReservationPresenter extends BasePresenter {
    /** @var IDelegateReservationFormWrapperFactory */
    private $delegateReservationFormFactory;

    /** @var ApplicationDao */
    private $applicationDao;

    public function __construct(IDelegateReservationFormWrapperFactory $delegateReservationFormWrapperFactory, ApplicationDao $applicationDao) {
        parent::__construct();
        $this->delegateReservationFormFactory = $delegateReservationFormWrapperFactory;
        $this->applicationDao = $applicationDao;
    }

    /**
     * @param string[] $ids
     */
    public function actionDelegate(array $ids = []) {
        $applications = $this->applicationDao->getApplicationsForReservationDelegation($ids);;
        /** @var DelegateReservationFormWrapper $delegateForm */
        $delegateForm = $this->getComponent('delegateForm');
        $delegateForm->setApplications($applications);
    }

    /**
     * @return \App\AdminModule\Controls\Forms\DelegateReservationFormWrapper
     */
    protected function createComponentDelegateForm() {
        return $this->delegateReservationFormFactory->create();
    }

}
