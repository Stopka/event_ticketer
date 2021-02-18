<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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
use Ticketer\Modules\FrontModule\Templates\EventTemplate;

/**
 * @method EventTemplate getTemplate()
 */
class EventPresenter extends BasePresenter
{

    public EventDao $eventDao;

    public ICartFormWrapperFactory $cartFormWrapperFactory;

    public ISubstituteFormWrapperFactory $substituteFormWrapperFactory;

    /**
     * EventPresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param EventDao $additionDao
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ISubstituteFormWrapperFactory $substituteFormWrapperFactory
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        EventDao $additionDao,
        ICartFormWrapperFactory $cartFormWrapperFactory,
        ISubstituteFormWrapperFactory $substituteFormWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $additionDao;
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteFormWrapperFactory = $substituteFormWrapperFactory;
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
     * @throws BadRequestException
     */
    public function actionRegister(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
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
        $template = $this->getTemplate();
        $template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionSubstitute(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
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
}
