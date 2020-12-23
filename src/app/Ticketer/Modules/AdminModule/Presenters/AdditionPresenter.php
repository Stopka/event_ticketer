<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
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
     * @param int $id
     * @throws AbortException
     */
    public function actionDefault(int $id): void
    {
        $event = $this->eventDao->getEvent($id);
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
     * @param int $id
     * @throws AbortException
     */
    public function actionAdd(int $id): void
    {
        $event = $this->eventDao->getEvent($id);
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
     * @param int $id
     * @throws AbortException
     */
    public function actionEdit(int $id): void
    {
        $addition = $this->additionDao->getAddition($id);
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
