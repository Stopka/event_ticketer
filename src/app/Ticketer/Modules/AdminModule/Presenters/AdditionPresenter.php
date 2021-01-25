<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\AdditionFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Forms\IAdditionFormWrapperFactory;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Modules\AdminModule\Controls\Grids\AdditionsGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\IAdditionsGridWrapperFactory;
use Ticketer\Model\Database\Daos\AdditionDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\EventEntity;

class AdditionPresenter extends BasePresenter
{

    private EventDao $eventDao;

    private AdditionDao $additionDao;

    private IAdditionsGridWrapperFactory $additionsGridWrapperFactory;

    private IAdditionFormWrapperFactory $additionFormWrapperFactory;

    public function __construct(
        BasePresenterDependencies $dependencies,
        EventDao $eventDao,
        AdditionDao $additionDao,
        IAdditionsGridWrapperFactory $additionsGridWrapperFactory,
        IAdditionFormWrapperFactory $additionFormWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $eventDao;
        $this->additionDao = $additionDao;
        $this->additionsGridWrapperFactory = $additionsGridWrapperFactory;
        $this->additionFormWrapperFactory = $additionFormWrapperFactory;
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
            $this->redirect("Event:edit");
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var AdditionsGridWrapper $additionsGrid */
        $additionsGrid = $this->getComponent('additionsGrid');
        $additionsGrid->setEventEntity($event);
        $this->template->event = $event;
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
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var AdditionFormWrapper $additionForm */
        $additionForm = $this->getComponent('additionForm');
        $additionForm->setEventEntity($event);
        $this->template->addition = null;
        $this->template->event = $event;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $addition = $this->additionDao->getAddition($uuid);
        if (null === $addition) {
            $this->flashTranslatedMessage('Error.Addition.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(AdditionEntity::class, $addition);
        /** @var AdditionFormWrapper $additionForm */
        $additionForm = $this->getComponent('additionForm');
        $additionForm->setAdditionEntity($addition);
        $this->template->addition = $addition;
        $this->template->event = $addition->getEvent();
    }

    public function createComponentAdditionsGrid(): AdditionsGridWrapper
    {
        return $this->additionsGridWrapperFactory->create();
    }

    public function createComponentAdditionForm(): AdditionFormWrapper
    {
        return $this->additionFormWrapperFactory->create();
    }
}
