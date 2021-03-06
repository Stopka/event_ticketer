<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\EarlyWaveFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Forms\IEarlyWaveFormWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Grids\EarlyWavesGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\IEarlyWavesGridWrapperFactory;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Database\Daos\EarlyWaveDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;

class EarlyWavePresenter extends BasePresenter
{

    private IEarlyWaveFormWrapperFactory $earlyWaveFormWrapperFactory;

    private IEarlyWavesGridWrapperFactory $earlyWavesGridWrapperFactory;

    private EarlyWaveDao $earlyWaveDao;

    private EventDao $eventDao;

    public function __construct(
        BasePresenterDependencies $dependencies,
        IEarlyWaveFormWrapperFactory $earlyWaveFormWrapperFactory,
        IEarlyWavesGridWrapperFactory $earlyWavesGridWrapperFactory,
        EarlyWaveDao $earlyWaveDao,
        EventDao $eventDao
    ) {
        parent::__construct($dependencies);
        $this->earlyWaveDao = $earlyWaveDao;
        $this->earlyWaveFormWrapperFactory = $earlyWaveFormWrapperFactory;
        $this->earlyWavesGridWrapperFactory = $earlyWavesGridWrapperFactory;
        $this->eventDao = $eventDao;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionDefault(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Event.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarlyWavesGridWrapper $grid */
        $grid = $this->getComponent('earlyWavesGrid');
        $grid->setEventEntity($event);
        $template = $this->getTemplate();
        $template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionAdd(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Event.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarlyWaveFormWrapper $earlyWaveForm */
        $earlyWaveForm = $this->getComponent('earlyWaveForm');
        $earlyWaveForm->setEventEntity($event);
        $template = $this->getTemplate();
        $template->earlyWave = null;
        $template->event = $earlyWaveForm->getEventEntity();
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $earlyWave = $this->earlyWaveDao->getEarlyWave($uuid);
        if (null === $earlyWave) {
            $this->flashTranslatedMessage('Error.EarlyWave.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EarlyWaveEntity::class, $earlyWave);
        /** @var EarlyWaveFormWrapper $earlyWaveForm */
        $earlyWaveForm = $this->getComponent('earlyWaveForm');
        $earlyWaveForm->setEarlyWaveEntity($earlyWave);
        $template = $this->getTemplate();
        $template->earlyWave = $earlyWave;
        $template->event = $earlyWaveForm->getEventEntity();
    }

    protected function createComponentEarlyWavesGrid(): EarlyWavesGridWrapper
    {
        return $this->earlyWavesGridWrapperFactory->create();
    }

    protected function createComponentEarlyWaveForm(): EarlyWaveFormWrapper
    {
        return $this->earlyWaveFormWrapperFactory->create();
    }
}
