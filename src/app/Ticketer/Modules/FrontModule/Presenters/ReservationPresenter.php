<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Controls\Forms\ICartFormWrapperFactory;
use Ticketer\Model\Database\Daos\ReservationDao;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\FrontModule\Templates\ReservationTemplate;

/**
 * @method ReservationTemplate getTemplate()
 */
class ReservationPresenter extends BasePresenter
{
    public ICartFormWrapperFactory $cartFormWrapperFactory;

    public ReservationDao $reservationDao;

    /**
     * SubstitutePresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ReservationDao $reservationDao
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        ICartFormWrapperFactory $cartFormWrapperFactory,
        ReservationDao $reservationDao
    ) {
        parent::__construct($dependencies);
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->reservationDao = $reservationDao;
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
     * @throws BadRequestException
     */
    public function actionRegister(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $reservation = $this->reservationDao->getRegisterReadyReservation($uuid);
        if (null === $reservation) {
            $this->flashTranslatedMessage('Error.Reservation.NotFound', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(ReservationEntity::class, $reservation);
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setReservation($reservation);
        $event = $reservation->getEvent();
        if (null === $event) {
            $this->flashTranslatedMessage('Error.Reservation.NotFound', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $template = $this->getTemplate();
        $template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm()
    {
        return $this->cartFormWrapperFactory->create();
    }
}
