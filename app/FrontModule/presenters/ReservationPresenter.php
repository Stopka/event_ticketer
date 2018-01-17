<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\Model\Persistence\Dao\ReservationDao;


class ReservationPresenter extends BasePresenter {

    /** @var ICartFormWrapperFactory */
    public $cartFormWrapperFactory;

    /** @var ReservationDao */
    public $reservationDao;

    /**
     * SubstitutePresenter constructor.
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ReservationDao $reservationDao
     */
    public function __construct(ICartFormWrapperFactory $cartFormWrapperFactory, ReservationDao $reservationDao) {
        parent::__construct();
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->reservationDao = $reservationDao;
    }

    /**
     * @param null $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault($id = null) {
        $this->redirect('register', $id);
    }

    /**
     * @param null $id
     * @throws \Nette\Application\AbortException
     */
    public function actionRegister($id = null) {
        $reservation = $this->reservationDao->getRegisterReadyReservation($id);
        if (!$reservation) {
            $this->flashTranslatedMessage('Error.Reservation.NotFound', self::FLASH_MESSAGE_TYPE_WARNING);
            $this->redirect('Homepage:');
        }
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setReservation($reservation);
        $event = $reservation->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm() {
        return $this->cartFormWrapperFactory->create();
    }

}
