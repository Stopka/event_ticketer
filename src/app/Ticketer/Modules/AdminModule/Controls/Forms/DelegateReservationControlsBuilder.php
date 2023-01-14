<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Nette\Forms\IControl;
use Ticketer\Controls\Forms\Form;
use Ticketer\Model\Database\Daos\ReservationDao;
use Ticketer\Model\Database\Entities\EventEntity;

class DelegateReservationControlsBuilder
{
    public const VALUE_DELEGATE_NEW = '*';
    public const CONTAINER_NAME_NEW = 'delegateNew';
    public const FIELD_DELEGATE = 'delegateTo';

    private ReservationDao $reservationDao;

    private EventEntity $event;

    public function __construct(EventEntity $eventEntity, ReservationDao $reservationDao)
    {
        $this->reservationDao = $reservationDao;
        $this->event = $eventEntity;
    }


    protected function getReservationDao(): ReservationDao
    {
        return $this->reservationDao;
    }

    public function appendDelegateControls(Form $form): void
    {
        $form->addGroup('Form.Reservation.Label.DelegatedPerson')
            ->setOption($form::OPTION_KEY_ID, 'reservationDelegate')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Reservation.Description.DelegatedPerson')
            ->setOption($form::OPTION_KEY_EMBED, 'Form.Reservation.Label.NewPerson');
        $reservations = $this->getReservationDao()->getEventReservationList($this->event);
        $delegateToSelection = [
            null => '',
            self::VALUE_DELEGATE_NEW => 'Form.Reservation.Label.NewPerson',
        ];
        if (count($reservations) > 0) {
            $delegateToSelection['Form.Reservation.Label.ExistingPerson'] = $reservations;
        }
        $form->addSelect(self::FIELD_DELEGATE, 'Form.Reservation.Label.Person', $delegateToSelection)
            ->setRequired()
            ->addCondition($form::EQUAL, self::VALUE_DELEGATE_NEW)
            ->toggle('delegateNewPerson');
        $form->addGroup('Form.Reservation.Label.NewPerson')
            ->setOption($form::OPTION_KEY_ID, 'delegateNewPerson');
        $new = $form->addContainer(self::CONTAINER_NAME_NEW);
        $new->addText("firstName", "Attribute.Person.FirstName", null, 255)
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, null, 255);
        $new->addText("lastName", "Attribute.Person.LastName", null, 255)
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, null, 255);
        /** @var IControl $delegateToControl */
        $delegateToControl = $form['delegateTo'];
        $new->addText("email", "Attribute.Person.Email")
            ->setRequired(false)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Reservation.Description.Email')
            ->setDefaultValue('@')
            ->addConditionOn($delegateToControl, $form::EQUAL, '*')
            ->addRule($form::FILLED)
            ->addRule($form::EMAIL);
    }
}
