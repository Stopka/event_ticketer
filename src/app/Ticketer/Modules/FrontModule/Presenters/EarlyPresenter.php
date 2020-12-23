<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Controls\Forms\ICartFormWrapperFactory;
use Ticketer\Modules\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use Ticketer\Modules\FrontModule\Controls\Forms\SubstituteFormWrapper;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\EarlyDao;
use Ticketer\Model\Database\Entities\EarlyEntity;

class EarlyPresenter extends BasePresenter
{

    public ICartFormWrapperFactory $cartFormWrapperFactory;

    public ISubstituteFormWrapperFactory $substituteFormWrapperFactory;

    public ApplicationDao $applicationDao;

    public EarlyDao $earlyDao;

    /**
     * EarlyWavePresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param ApplicationDao $applicationDao
     * @param EarlyDao $earlyDao
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        ICartFormWrapperFactory $cartFormWrapperFactory,
        ISubstituteFormWrapperFactory $substituteFormWrapperFactory,
        ApplicationDao $applicationDao,
        EarlyDao $earlyDao
    ) {
        parent::__construct($dependencies);
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
        $this->applicationDao = $applicationDao;
        $this->earlyDao = $earlyDao;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionDefault(string $id): void
    {
        $this->redirect('register', $id);
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionRegister(string $id): void
    {
        $early = $this->earlyDao->getReadyEarlyByUid($id);
        if (null === $early) {
            $this->flashTranslatedMessage('Error.Early.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EarlyEntity::class, $early);
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setEarly($early);
        $earlyWave = $early->getEarlyWave();
        if (null === $earlyWave) {
            $this->flashTranslatedMessage('Error.Early.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $event = $earlyWave->getEvent();
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Early.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        if ($event->isCapacityFull()) {
            $this->flashTranslatedMessage('Error.Event.Full', FlashMessageTypeEnum::WARNING());
            $this->redirect('substitute', $id);
        }
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionSubstitute(string $id): void
    {
        $early = $this->earlyDao->getReadyEarlyByUid($id);
        if (null === $early) {
            $this->flashTranslatedMessage('Error.Early.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EarlyEntity::class, $early);
        /** @var SubstituteFormWrapper $substituteFormWrapper */
        $substituteFormWrapper = $this->getComponent('substituteForm');
        $substituteFormWrapper->setEarly($early);
        $earlyWave = $early->getEarlyWave();
        if (null === $earlyWave) {
            $this->flashTranslatedMessage('Error.Early.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $event = $earlyWave->getEvent();
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Early.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        if (!$event->isCapacityFull()) {
            $this->redirect('register', $id);
        }
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm()
    {
        return $this->cartFormWrapperFactory->create();
    }

    /**
     * @return SubstituteFormWrapper
     */
    protected function createComponentSubstituteForm()
    {
        return $this->substituteFormWrapperFactory->create();
    }
}
