<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\FrontModule\Controls\Forms\SubstituteFormWrapper;
use App\FrontModule\Controls\IOccupancyControlFactory;
use App\FrontModule\Controls\OccupancyControl;
use App\Model\Persistence\Dao\EventDao;


class EventPresenter extends BasePresenter {

    /** @var EventDao */
    public $eventDao;

    /** @var ICartFormWrapperFactory */
    public $cartFormWrapperFactory;

    /** @var ISubstituteFormWrapperFactory */
    public $substituteFormWrapperFactory;

    /** @var  IOccupancyControlFactory */
    public $occupancyControlFactory;

    /**
     * EventPresenter constructor.
     * @param EventDao $additionDao
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param IOccupancyControlFactory $occupancyControlFactory
     */
    public function __construct(EventDao $additionDao, ICartFormWrapperFactory $cartFormWrapperFactory, ISubstituteFormWrapperFactory $substituteFormWrapperFactory, IOccupancyControlFactory $occupancyControlFactory) {
        parent::__construct();
        $this->eventDao = $additionDao;
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
        $this->occupancyControlFactory = $occupancyControlFactory;
    }

    /**
     * @param int|null $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(?int $id = null) {
        if (!$id) {
            $events = $this->eventDao->getPublicAvailibleEvents();
            $eventsCount = count($events);
            if ($eventsCount < 1) {
                $this->flashTranslatedMessage("Presenter.Front.Event.Default.Message.NoEvents", self::FLASH_MESSAGE_TYPE_WARNING);
            }
            if ($eventsCount > 1) {
                $this->flashTranslatedMessage("Presenter.Front.Event.Default.Message.MultipleEvents", self::FLASH_MESSAGE_TYPE_INFO);
            }
            if ($eventsCount !== 1) {
                $this->redirect('Homepage:default');
            }
            $id = $events[0]->getId();
        }
        $this->redirect('register', $id);
    }

    /**
     * @param null $id
     * @throws \Nette\Application\AbortException
     */
    public function actionRegister($id = null) {
        $event = $this->eventDao->getEvent($id);
        if (!$event || !$event->isActive()) {
            $this->flashTranslatedMessage('Error.Event.NotFound', self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        if (!$event->isStarted()) {
            $this->flashTranslatedMessage('Error.Event.NotReady', self::FLASH_MESSAGE_TYPE_WARNING);
            $this->redirect('Homepage:');
        }
        if ($event->isCapacityFull()) {
            $this->flashTranslatedMessage('Error.Event.Full', self::FLASH_MESSAGE_TYPE_WARNING);
            $this->redirect('substitute', $id);
        }
        /** @var CartFormWrapper $cartForm */
        $cartForm = $this->getComponent('cartForm');
        $cartForm->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @param null $id
     * @throws \Nette\Application\AbortException
     */
    public function actionSubstitute($id = null) {
        $event = $this->eventDao->getPublicAvailibleEvent($id);
        if (!$event) {
            $this->flashTranslatedMessage('Error.Event.NotFound', self::FLASH_MESSAGE_TYPE_ERROR);
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
     * @return CartFormWrapper
     */
    protected function createComponentCartForm() {
        return $this->cartFormWrapperFactory->create();
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
    protected function createComponentOccupancy() {
        return $this->occupancyControlFactory->create();
    }

    /**
     * @param null|int $id
     * @throws \Nette\Application\AbortException
     */
    public function renderOccupancy(?int $id = null) {
        if (!$id) {
            $events = $this->eventDao->getPublicAvailibleEvents();
            if ($events) {
                $this->redirect('this', $events[0]->getId());
                return;
            }
            $events = $this->eventDao->getPublicFutureEvents();
            if ($events) {
                $this->redirect('this', $events[0]->getId());
                return;
            }
        }
        $event = $this->eventDao->getEvent($id);
        $this->template->event = $event;
        if ($event) {
            /** @var OccupancyControl $occupancy */
            $occupancy = $this->getComponent('occupancy');
            $occupancy->setEvent($event);
        }
    }

}
