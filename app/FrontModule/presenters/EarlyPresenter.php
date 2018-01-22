<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use App\FrontModule\Controls\Forms\SubstituteFormWrapper;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EarlyDao;
use App\Model\Persistence\Entity\EarlyEntity;


class EarlyPresenter extends BasePresenter {

    /** @var ICartFormWrapperFactory */
    public $cartFormWrapperFactory;

    /** @var ISubstituteFormWrapperFactory */
    public $substituteFormWrapperFactory;

    /** @var ApplicationDao */
    public $applicationDao;

    /** @var EarlyDao */
    public $earlyDao;

    /**
     * EarlyPresenter constructor.
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param ApplicationDao $applicationDao
     * @param EarlyDao $earlyDao
     */
    public function __construct(ICartFormWrapperFactory $cartFormWrapperFactory, ISubstituteFormWrapperFactory $substituteFormWrapperFactory, ApplicationDao $applicationDao, EarlyDao $earlyDao) {
        parent::__construct();
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
        $this->applicationDao = $applicationDao;
        $this->earlyDao = $earlyDao;
    }

    /**
     * @param string $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(string $id) {
        $this->redirect('register', $id);
    }

    /**
     * @param string $id
     * @throws \Nette\Application\AbortException
     */
    public function actionRegister(string $id) {
        $early = $this->earlyDao->getReadyEarlyByUid($id);
        if (!$early) {
            $this->flashTranslatedMessage('Error.Early.NotReady', self::FLASH_MESSAGE_TYPE_WARNING);
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EarlyEntity::class, $early);
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if ($event->isCapacityFull()) {
            $this->flashTranslatedMessage('Error.Cart.Full', 'warning');
            $this->redirect('substitute', $id);
        }
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws \Nette\Application\AbortException
     */
    public function actionSubstitute(string $id) {
        $early = $this->earlyDao->getReadyEarlyByUid($id);
        if (!$early) {
            $this->flashTranslatedMessage('Error.Early.NotReady', self::FLASH_MESSAGE_TYPE_WARNING);
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EarlyEntity::class, $early);
        /** @var SubstituteFormWrapper $substituteFormWrapper */
        $substituteFormWrapper = $this->getComponent('substituteForm');
        $substituteFormWrapper->setEarly($early);
        $event = $early->getEarlyWave()->getEvent();
        if (!$event->isCapacityFull()) {
            $this->redirect('register', $id);
        }
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

}
