<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Modules\AdminModule\Controls\Grids\ISubstitutesGridWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Grids\SubstitutesGridWrapper;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;

class SubstitutePresenter extends BasePresenter
{

    /** @var  ISubstitutesGridWrapperFactory */
    public ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory;

    /** @var EventDao */
    public EventDao $eventDao;

    /**
     * SubstitutePresenter constructor.
     * @param ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory
     * @param EventDao $additionDao
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory,
        EventDao $additionDao
    ) {
        parent::__construct($dependencies);
        $this->substitutesGridWrapperFactory = $substitutesGridWrapperFactory;
        $this->eventDao = $additionDao;
    }

    /**
     * @param int $id
     * @throws AbortException
     */
    public function actionDefault(int $id): void
    {
        $event = $this->eventDao->getEvent($id);
        if (null === $event) {
            $this->flashTranslatedMessage("Error.Event.NotFound", FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var SubstitutesGridWrapper $substitutesGridWrapper */
        $substitutesGridWrapper = $this->getComponent('substitutesGrid');
        $substitutesGridWrapper->setEvent($event);
        $this->template->event = $event;
    }

    protected function createComponentSubstitutesGrid(): SubstitutesGridWrapper
    {
        return $this->substitutesGridWrapperFactory->create();
    }
}
