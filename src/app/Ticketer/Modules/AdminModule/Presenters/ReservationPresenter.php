<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\BadRequestException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\DelegateReservationFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Forms\IDelegateReservationFormWrapperFactory;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;
use Nette\Application\AbortException;

class ReservationPresenter extends BasePresenter
{
    private IDelegateReservationFormWrapperFactory $delegateReservationFormFactory;

    private ApplicationDao $applicationDao;

    private EventDao $eventDao;

    public function __construct(
        BasePresenterDependencies $dependencies,
        IDelegateReservationFormWrapperFactory $delegateReservationFormWrapperFactory,
        ApplicationDao $applicationDao,
        EventDao $eventDao
    ) {
        parent::__construct($dependencies);
        $this->delegateReservationFormFactory = $delegateReservationFormWrapperFactory;
        $this->applicationDao = $applicationDao;
        $this->eventDao = $eventDao;
    }

    /**
     * @param string $id    event id
     * @param string[] $ids applications
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionDelegate(string $id, array $ids = []): void
    {
        $uuid = $this->deserializeUuid($id);
        $event = $this->eventDao->getEvent($uuid);
        if (null === $event) {
            $this->flashTranslatedMessage("Error.Event.NotFound", FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $applications = $this->applicationDao->getApplicationsForReservationDelegation($ids, $event);
        /** @var DelegateReservationFormWrapper $delegateForm */
        $delegateForm = $this->getComponent('delegateForm');
        $delegateForm->setApplications($applications);
    }

    protected function createComponentDelegateForm(): DelegateReservationFormWrapper
    {
        return $this->delegateReservationFormFactory->create();
    }
}
