<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Mpdf\Tag\U;
use Nette\Application\AbortException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\EarlyFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Forms\IEarlyFormWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Grids\EarliesGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\IEarliesGridWrapperFactory;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Database\Daos\EarlyDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;

class EarlyPresenter extends BasePresenter
{

    private EventDao $eventDao;

    private IEarliesGridWrapperFactory $earliesGridWrapperFactory;

    private IEarlyFormWrapperFactory $earlyFormWrapperFactory;

    private EarlyDao $earlyDao;

    public function __construct(
        BasePresenterDependencies $dependencies,
        EventDao $eventDao,
        IEarliesGridWrapperFactory $earliesGridWrapperFactory,
        IEarlyFormWrapperFactory $earlyFormWrapperFactory,
        EarlyDao $earlyDao
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $eventDao;
        $this->earlyDao = $earlyDao;
        $this->earliesGridWrapperFactory = $earliesGridWrapperFactory;
        $this->earlyFormWrapperFactory = $earlyFormWrapperFactory;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionDefault(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->redirect("Event:edit");
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarliesGridWrapper $earliesGrid */
        $earliesGrid = $this->getComponent('earliesGrid');
        $earliesGrid->setEventEntity($event);
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionAdd(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Event.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarlyFormWrapper $earlyForm */
        $earlyForm = $this->getComponent('earlyForm');
        $earlyForm->setEventEntity($event);
        $this->template->early = null;
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionEdit(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $early = $this->earlyDao->getEarly($uuid);
        if (null === $early) {
            $this->flashTranslatedMessage('Error.Addition.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EarlyEntity::class, $early);
        /** @var EarlyFormWrapper $earlyForm */
        $earlyForm = $this->getComponent('earlyForm');
        $earlyForm->setEarlyEntity($early);
        $this->template->early = $early;
        $this->template->event = $earlyForm->getEventEntity();
    }

    protected function createComponentEarliesGrid(): EarliesGridWrapper
    {
        return $this->earliesGridWrapperFactory->create();
    }

    protected function createComponentEarlyForm(): EarlyFormWrapper
    {
        return $this->earlyFormWrapperFactory->create();
    }
}
