<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Controls\Forms\ICartFormWrapperFactory;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\FrontModule\Controls\Forms\ISubstituteFormWrapperFactory;
use Ticketer\Modules\FrontModule\Controls\Forms\SubstituteFormWrapper;
use Ticketer\Modules\FrontModule\Controls\IOccupancyControlFactory;
use Ticketer\Modules\FrontModule\Controls\OccupancyControl;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;

class EventPresenter extends BasePresenter
{

    public EventDao $eventDao;

    public ICartFormWrapperFactory $cartFormWrapperFactory;

    public ISubstituteFormWrapperFactory $substituteFormWrapperFactory;

    public IOccupancyControlFactory $occupancyControlFactory;

    /**
     * EventPresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param EventDao $additionDao
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     * @param IOccupancyControlFactory $occupancyControlFactory
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        EventDao $additionDao,
        ICartFormWrapperFactory $cartFormWrapperFactory,
        ISubstituteFormWrapperFactory $substituteFormWrapperFactory,
        IOccupancyControlFactory $occupancyControlFactory
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $additionDao;
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
        $this->occupancyControlFactory = $occupancyControlFactory;
    }

    /**
     * @param string|null $id
     * @throws AbortException
     */
    public function actionDefault(?string $id = null): void
    {
        if (null === $id) {
            $events = $this->eventDao->getPublicAvailibleEvents();
            $eventsCount = count($events);
            if ($eventsCount < 1) {
                $this->flashTranslatedMessage(
                    "Presenter.Front.Event.Default.Message.NoEvents",
                    FlashMessageTypeEnum::WARNING()
                );
            }
            if ($eventsCount > 1) {
                $this->flashTranslatedMessage(
                    "Presenter.Front.Event.Default.Message.MultipleEvents",
                    FlashMessageTypeEnum::INFO()
                );
            }
            if (1 !== $eventsCount) {
                $this->redirect('Homepage:default');
            }
            $id = $events[0]->getId()->toString();
        }
        $this->redirect('register', $id);
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionRegister(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event || !$event->isActive()) {
            $this->flashTranslatedMessage('Error.Event.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        if (!$event->isStarted()) {
            $this->flashTranslatedMessage('Error.Event.NotReady', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        if ($event->isCapacityFull()) {
            $this->flashTranslatedMessage('Error.Event.Full', FlashMessageTypeEnum::WARNING());
            $this->redirect('substitute', $id);
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var CartFormWrapper $cartForm */
        $cartForm = $this->getComponent('cartForm');
        $cartForm->setEvent($event);
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionSubstitute(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getPublicAvailibleEvent($uuid);
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Event.NotFound', FlashMessageTypeEnum::ERROR());
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
    protected function createComponentCartForm(): CartFormWrapper
    {
        return $this->cartFormWrapperFactory->create();
    }

    /**
     * @return SubstituteFormWrapper
     */
    protected function createComponentSubstituteForm(): SubstituteFormWrapper
    {
        return $this->substituteFormWrapperFactory->create();
    }

    /**
     * @return OccupancyControl
     */
    protected function createComponentOccupancy(): OccupancyControl
    {
        return $this->occupancyControlFactory->create();
    }

    /**
     * @param string|null $id
     * @param bool $showHeaders
     * @throws AbortException
     */
    public function renderOccupancy(?string $id = null, bool $showHeaders = true): void
    {
        if (null === $id) {
            $events = $this->eventDao->getPublicAvailibleEvents();
            if (count($events) > 0) {
                $this->redirect('this', $events[0]->getId()->toString());
            }
            $events = $this->eventDao->getPublicFutureEvents();
            if (count($events) > 0) {
                $this->redirect('this', $events[0]->getId()->toString());
            }
            $event = null;
        } else {
            $uuid = Uuid::fromString($id);
            $event = $this->eventDao->getEvent($uuid);
        }
        $template = $this->getTemplate();
        $template->event = $event;
        $template->showHeaders = $showHeaders;
        if (null !== $event) {
            /** @var OccupancyControl $occupancy */
            $occupancy = $this->getComponent('occupancy');
            $occupancy->setEvent($event);
        }
    }
}
