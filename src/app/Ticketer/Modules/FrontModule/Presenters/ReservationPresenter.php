<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Controls\Forms\ICartFormWrapperFactory;
use Ticketer\Model\Database\Daos\ReservationDao;
use Ticketer\Model\Database\Entities\ReservationEntity;

class ReservationPresenter extends BasePresenter
{

    public ICartFormWrapperFactory $cartFormWrapperFactory;

    public ReservationDao $reservationDao;

    /**
     * SubstitutePresenter constructor.
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
     */
    public function actionRegister(string $id): void
    {
        $reservation = $this->reservationDao->getRegisterReadyReservationByUid($id);
        if (null === $reservation) {
            $this->flashTranslatedMessage('Error.Reservation.NotFound', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(ReservationEntity::class, $reservation);
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setReservation($reservation);
        $event = $reservation->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm()
    {
        return $this->cartFormWrapperFactory->create();
    }
}
