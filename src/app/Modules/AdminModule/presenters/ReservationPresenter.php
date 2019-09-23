<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\DelegateReservationFormWrapper;
use App\AdminModule\Controls\Forms\IDelegateReservationFormWrapperFactory;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;
use Nette\Application\AbortException;

class ReservationPresenter extends BasePresenter {
    /** @var IDelegateReservationFormWrapperFactory */
    private $delegateReservationFormFactory;

    /** @var ApplicationDao */
    private $applicationDao;

    /** @var EventEntity */
    private $eventDao;

    public function __construct(IDelegateReservationFormWrapperFactory $delegateReservationFormWrapperFactory, ApplicationDao $applicationDao, EventDao $eventDao) {
        parent::__construct();
        $this->delegateReservationFormFactory = $delegateReservationFormWrapperFactory;
        $this->applicationDao = $applicationDao;
        $this->eventDao = $eventDao;
    }

    /**
     * @param int $id event id
     * @param int[] $ids applications
     * @throws AbortException
     */
    public function actionDelegate(int $id, array $ids = []) {
        $event = $this->eventDao->getEvent($id);
        if (!$event) {
            $this->flashTranslatedMessage("Error.Event.NotFound", self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        $applications = $this->applicationDao->getApplicationsForReservationDelegation($ids, $event);
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
