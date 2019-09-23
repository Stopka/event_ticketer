<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Model\Persistence\Dao\ReservationDao;
use App\Model\Persistence\Entity\EventEntity;

class DelegateReservationControlsBuilder {

    const VALUE_DELEGATE_NEW = '*';
    const CONTAINER_NAME_NEW = 'delegateNew';
    const FIELD_DELEGATE = 'delegateTo';

    /** @var ReservationDao */
    private $reservationDao;

    /** @var EventEntity */
    private $event;

    public function __construct(EventEntity $eventEntity, ReservationDao $reservationDao) {
        $this->reservationDao = $reservationDao;
        $this->event = $eventEntity;
    }


    protected function getReservationDao(): ReservationDao {
        return $this->reservationDao;
    }

    public function appendDelegateControls(Form $form) {
        $form->addGroup('Form.Reservation.Label.DelegatedPerson')
            ->setOption($form::OPTION_KEY_ID, 'reservationDelegate')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Reservation.Description.DelegatedPerson')
            ->setOption($form::OPTION_KEY_EMBED, 'Form.Reservation.Label.NewPerson');
        $reservations = $this->getReservationDao()->getEventReservationList($this->event);
        $delegateToSelection = [
            NULL => '',
            self::VALUE_DELEGATE_NEW => 'Form.Reservation.Label.NewPerson'
        ];
        if ($reservations) {
            $delegateToSelection['Form.Reservation.Label.ExistingPerson'] = $reservations;
        }
        $form->addSelect(self::FIELD_DELEGATE, 'Form.Reservation.Label.Person', $delegateToSelection)
            ->setRequired()
            ->addCondition($form::EQUAL, self::VALUE_DELEGATE_NEW)
            ->toggle('delegateNewPerson');
        $form->addGroup('Form.Reservation.Label.NewPerson')
            ->setOption($form::OPTION_KEY_ID, 'delegateNewPerson');
        $new = $form->addContainer(self::CONTAINER_NAME_NEW);
        $new->addText("firstName", "Attribute.Person.FirstName", NULL, 255)
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $new->addText("lastName", "Attribute.Person.LastName", NULL, 255)
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, NULL, 255);
        /** @noinspection PhpParamsInspection */
        $new->addText("email", "Attribute.Person.Email")
            ->setRequired(false)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Reservation.Description.Email')
            ->setDefaultValue('@')
            ->addConditionOn($form['delegateTo'], $form::EQUAL, '*')
            ->addRule($form::FILLED)
            ->addRule($form::EMAIL);
    }
}