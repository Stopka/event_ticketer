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

trait TAppendDelegateReservation {

    abstract protected function getReservationDao(): ReservationDao;

    protected function appendDelegateControls(Form $form) {
        $form->addGroup('Form.Reservation.Label.DelegatedPerson')
            ->setOption($form::OPTION_KEY_ID, 'reservationDelegate')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Reservation.Description.DelegatedPerson')
            ->setOption($form::OPTION_KEY_EMBED, 'Form.Reservation.Label.NewPerson');
        $form->addSelect('delegateTo', 'Form.Reservation.Label.Person', [
            NULL => '',
            '*' => 'Form.Reservation.Label.NewPerson',
            'Form.Reservation.Label.ExistingPerson' => $this->getReservationDao()->getEventReservationList($this->event)
        ])
            ->setRequired()
            ->addCondition($form::EQUAL, '*')
            ->toggle('delegateNewPerson');
        $form->addGroup('Form.Reservation.Label.NewPerson')
            ->setOption($form::OPTION_KEY_ID, 'delegateNewPerson');
        $new = $form->addContainer('delegateNew');
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