<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Exception;
use Nette\Application\AbortException;
use RuntimeException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\AdditionsControlsBuilder;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Controls\Forms\IAdditionsControlsBuilderFactory;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Daos\ReservationDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\AdditionVisibilityEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\ReservationManager;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;

class ReserveApplicationFormWrapper extends FormWrapper
{

    public const FIELD_COUNT = "count";

    private ?EventEntity $event;

    private CurrencyEntity $currency;

    private CurrencyDao $currencyDao;

    private ReservationManager $reservationManager;

    private IAdditionsControlsBuilderFactory $additionsControlsBuilderFactory;

    private ?AdditionsControlsBuilder $additionsControlsBuilder = null;

    private IDelegateReservationControlsBuilderFactory $delegateReservationControlsBuilderFactory;

    private ?DelegateReservationControlsBuilder $delegateReservationControlsBuilder = null;

    /** @var ApplicationEntity[] */
    private array $applicationEntities = [];

    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        CurrencyDao $currencyDao,
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

    public function getDelegateReservationControlsBuilder(): DelegateReservationControlsBuilder
    {
        if (null === $this->event) {
            throw new RuntimeException('Missing event');
        }
        if (null === $this->delegateReservationControlsBuilder) {
            $builder = $this->delegateReservationControlsBuilderFactory->create($this->event);
            $this->delegateReservationControlsBuilder = $builder;
        }

        return $this->delegateReservationControlsBuilder;
    }

    public function getAdditionsControlsBuilder(): AdditionsControlsBuilder
    {
        if (null === $this->event) {
            throw new RuntimeException('Missing entity');
        }
        if (null === $this->additionsControlsBuilder) {
            $builder = $this->additionsControlsBuilderFactory->create(
                $this->event,
                $this->currency
            )
                ->setVisibilityResolver(
                    static function (AdditionVisibilityEntity $visibility): bool {
                        return $visibility->isReservation();
                    }
                )
                ->setVisibleCountLeft()
                ->setAdmin();
            //->disableMinimum();
            $this->additionsControlsBuilder = $builder;
        }

        return $this->additionsControlsBuilder;
    }

    public function setEvent(EventEntity $event): void
    {
        $this->event = $event;
    }


    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $this->appendReserveControls($form);
        $form->addGroup('Entity.Plural.Choice')
            ->setOption($form::OPTION_KEY_CLASS, 'price_subspace');
        $this->getAdditionsControlsBuilder()
            ->appendAdditionsControls($form);
        $this->appendDelegateControls($form);
        $this->appendSubmitControls(
            $form,
            count($this->applicationEntities) > 0 ? 'Form.Action.Edit' : 'Form.Action.Reserve',
            [$this, 'reserveClicked']
        );
    }

    protected function appendReserveControls(Form $form): void
    {
        if (count($this->applicationEntities) > 0) {
            return;
        }
        $form->addGroup('Entity.Singular.Cart')
            ->setOption('visual', false);
        $form->addText(self::FIELD_COUNT, 'Attribute.Count', null, 255)
            ->setHtmlType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, 100]);
    }

    protected function appendDelegateControls(Form $form): void
    {
        if (count($this->applicationEntities) > 0) {
            return;
        }
        $this->getDelegateReservationControlsBuilder()
            ->appendDelegateControls($form);
        /** @var SelectBox $delegateSelect */
        $delegateSelect = $form[DelegateReservationControlsBuilder::FIELD_DELEGATE];
        $delegateSelect->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Reservation.Description.Delegated")
            ->setRequired(false);
        $items = $delegateSelect->getItems();
        $items[null] = "Form.Reservation.Label.DoNotDelegate";
        $delegateSelect->setItems($items);
    }

    /**
     * @param array<mixed> $values
     * @return array<mixed>
     */
    public function processValues(array $values): array
    {
        $values = $this->getAdditionsControlsBuilder()->preprocessAdditionsValues($values);

        return $values;
    }

    /**
     * @param SubmitButton $button
     * @throws Exception
     * @throws AbortException
     */
    public function reserveClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form || null === $this->event) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $values = $this->processValues($values);
        if (count($this->applicationEntities) > 0) {
            $this->reservationManager->createReservedApplicationsFromReservationForm($values, $this->event);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Reservation.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
        } else {
            $this->reservationManager->editReservedApplicationsFromReservationForm($values, $this->applicationEntities);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Reservation.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
        }
        $this->getPresenter()->redirect('Application:', $this->event->getId());
    }

    /**
     * @param ApplicationEntity[] $applications
     */
    public function setApplications(array $applications): void
    {
        $this->applicationEntities = $applications;
    }
}
