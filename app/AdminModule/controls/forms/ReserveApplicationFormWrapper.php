<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\AdditionsControlsBuilder;
use App\Controls\Forms\Form;
use App\Controls\Forms\FormWrapperDependencies;
use App\Controls\Forms\IAdditionsControlsBuilderFactory;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\ReservationDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\ReservationManager;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;

class ReserveApplicationFormWrapper extends FormWrapper {

    const FIELD_COUNT = "count";

    /** @var  EventEntity */
    private $event;

    /** @var  CurrencyEntity */
    private $currency;

    /** @var  CurrencyDao */
    private $currencyDao;

    /** @var  ReservationManager */
    private $reservationManager;

    /** @var IAdditionsControlsBuilderFactory */
    private $additionsControlsBuilderFactory;

    /** @var AdditionsControlsBuilder */
    private $additionsControlsBuilder;

    /** @var IDelegateReservationControlsBuilderFactory */
    private $delegateReservationControlsBuilderFactory;

    /** @var DelegateReservationControlsBuilder */
    private $delegateReservationControlsBuilder;

    /** @var ApplicationEntity[] */
    private $applicationEntities = [];

    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        CurrencyDao $currencyDao,
        ReservationDao $reservationDao,
        ReservationManager $reservationManager,
        IAdditionsControlsBuilderFactory $additionsControlsBuilderFactory,
        IDelegateReservationControlsBuilderFactory $delegateReservationControlsBuilderFactory
    ) {
        parent::__construct($formWrapperDependencies);
        $this->currencyDao = $currencyDao;
        $this->currency = $this->currencyDao->getDefaultCurrency();
        $this->reservationManager = $reservationManager;
        $this->additionsControlsBuilderFactory = $additionsControlsBuilderFactory;
        $this->delegateReservationControlsBuilderFactory = $delegateReservationControlsBuilderFactory;
    }

    /**
     * @return DelegateReservationControlsBuilder
     */
    public function getDelegateReservationControlsBuilder(): DelegateReservationControlsBuilder {
        if (!$this->delegateReservationControlsBuilder) {
            $builder = $this->delegateReservationControlsBuilderFactory->create($this->event);
            $this->delegateReservationControlsBuilder = $builder;
        }
        return $this->delegateReservationControlsBuilder;
    }

    /**
     * @return AdditionsControlsBuilder
     */
    public function getAdditionsControlsBuilder(): AdditionsControlsBuilder {
        if (!$this->additionsControlsBuilder) {
            $builder = $this->additionsControlsBuilderFactory->create(
                $this->event,
                $this->currency
            )
                ->setVisibilityPlace(AdditionEntity::VISIBLE_RESERVATION)
                ->setVisibleCountLeft()
                ->setAdmin();
            //->disableMinimum();
            $this->additionsControlsBuilder = $builder;
        }
        return $this->additionsControlsBuilder;
    }

    public function setEvent(EventEntity $event) {
        $this->event = $event;
    }


    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $this->appendReserveControls($form);
        $form->addGroup('Entity.Plural.Choice')
            ->setOption($form::OPTION_KEY_CLASS, 'price_subspace');
        $this->getAdditionsControlsBuilder()
            ->appendAdditionsControls($form);
        $this->appendDelegateControls($form);
        $this->appendSubmitControls($form,
            count($this->applicationEntities) ? 'Form.Action.Edit' : 'Form.Action.Reserve',
            [$this, 'reserveClicked']);
    }

    protected function appendReserveControls(Form $form) {
        if (count($this->applicationEntities)) {
            return;
        }
        $form->addGroup('Entity.Singular.Cart')
            ->setOption('visual', false);
        $form->addText(self::FIELD_COUNT, 'Attribute.Count', null, 255)
            ->setType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, NULL, [1, 100]);
    }

    protected function appendDelegateControls(Form $form) {
        if (count($this->applicationEntities)) {
            return;
        }
        $this->getDelegateReservationControlsBuilder()
            ->appendDelegateControls($form);
        /** @var SelectBox $delegateSelect */
        $delegateSelect = $form[DelegateReservationControlsBuilder::FIELD_DELEGATE];
        $delegateSelect->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Reservation.Description.Delegated")
            ->setRequired(false);
        $items = $delegateSelect->getItems();
        $items[NULL] = "Form.Reservation.Label.DoNotDelegate";
        $delegateSelect->setItems($items);
    }

    public function processValues(array $values): array {
        $values = $this->getAdditionsControlsBuilder()->preprocessAdditionsValues($values);
        return $values;
    }

    /**
     * @param SubmitButton $button
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    public function reserveClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $this->processValues($values);
        if (!$this->applicationEntities) {
            $this->reservationManager->createReservedApplicationsFromReservationForm($values, $this->event);
            $this->getPresenter()->flashTranslatedMessage('Form.Reservation.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
        } else {
            $this->reservationManager->editReservedApplicationsFromReservationForm($values, $this->applicationEntities);
            $this->getPresenter()->flashTranslatedMessage('Form.Reservation.Message.Edit.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
        }
        $this->getPresenter()->redirect('Application:', $this->event->getId());
    }

    /**
     * @param ApplicationEntity[] $applications
     */
    public function setApplications(array $applications) {
        $this->applicationEntities = $applications;
    }

}