<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Controls\Forms\FormWrapperDependencies;
use App\Controls\Forms\TAppendAdditionsControls;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\ReservationDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\ReservationManager;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;

class ReserveApplicationFormWrapper extends FormWrapper {
    use TAppendAdditionsControls,
        TAppendDelegateReservation {
        appendDelegateControls as protected appendParentDelegateControls;
    }

    /** @var  EventEntity */
    private $event;

    /** @var  CurrencyEntity */
    private $currency;

    /** @var  CurrencyDao */
    private $currencyDao;

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  ReservationManager */
    private $reservationManager;

    /** @var ReservationDao */
    private $reservationDao;

    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        ApplicationDao $applicationDao,
        CurrencyDao $currencyDao,
        ReservationManager $reservationManager,
        ReservationDao $reservationDao
    ) {
        parent::__construct($formWrapperDependencies);
        $this->applicationDao = $applicationDao;
        $this->currencyDao = $currencyDao;
        $this->currency = $this->currencyDao->getDefaultCurrency();
        $this->reservationManager = $reservationManager;
        $this->reservationDao = $reservationDao;
        $this->setVisibilityPlace(AdditionEntity::VISIBLE_RESERVATION);
        $this->setVisibleCountLeft();
    }

    public function setEvent(EventEntity $event) {
        $this->event = $event;
    }

    public function isAdmin() {
        return true;
    }


    protected function getEvent() {
        return $this->event;
    }

    protected function getCurrency() {
        return $this->currency;
    }

    protected function getApplicationDao() {
        return $this->applicationDao;
    }

    protected function getReservationDao(): ReservationDao {
        return $this->reservationDao;
    }


    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $form->addGroup('Entity.Singular.Cart')
            ->setOption('visual', false);
        $form->addText('count', 'Attribute.Count', null, 255)
            ->setType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, NULL, [1, 100]);
        $form->addGroup('Entity.Plural.Choice')
            ->setOption('class', 'price_subspace');
        $this->appendAdditionsControls($form, $form, 1);
        $this->appendDelegateControls($form);
        $this->appendSubmitControls($form, 'Form.Action.Reserve', [$this, 'reserveClicked']);
    }

    protected function appendDelegateControls(Form $form) {
        $this->appendParentDelegateControls($form);
        /** @var SelectBox $delegateSelect */
        $delegateSelect = $form['delegateTo'];
        $delegateSelect->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Reservation.Description.Delegated")
            ->setRequired(false);
        $items = $delegateSelect->getItems();
        $items[NULL] = "Form.Reservation.Label.DoNotDelegate";
        $delegateSelect->setItems($items);
    }

    /**
     * @param SubmitButton $button
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function reserveClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $this->reservationManager->createReservedApplicationsFromReservationForm($values, $this->event);
        $this->getPresenter()->flashTranslatedMessage('Form.Reservation.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
        $this->getPresenter()->redirect('Application:', $this->event->getId());
    }

}