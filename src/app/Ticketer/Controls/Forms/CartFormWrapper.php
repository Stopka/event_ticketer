<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use DateTime;
use Exception;
use Nette\Application\AbortException;
use RuntimeException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Exceptions\EmptyException;
use Ticketer\Model\Exceptions\FormControlException;
use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Database\Attributes\GenderEnum;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Daos\InsuranceCompanyDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\Managers\CartManager;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Vodacek\Forms\Controls\DateInput;

class CartFormWrapper extends FormWrapper
{
    use TRecalculateControl;

    public const CONTAINER_NAME_APPLICATIONS = 'applications';
    public const CONTAINER_NAME_APPLICATION = 'application';
    public const CONTAINER_NAME_COMMONS = 'commons';
    public const VALUE_APPLICATION_NEW = 'new';

    /** @var  CartManager */
    private $cartManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var InsuranceCompanyDao */
    private $insuranceCompanyDao;

    /** @var  CurrencyEntity */
    protected $currency;

    /** @var  EarlyEntity|null */
    private $early;

    /** @var  EventEntity */
    private $event;

    /** @var  SubstituteEntity|null */
    private $substitute;

    /** @var  CartEntity|null */
    private $cart;

    /** @var ReservationEntity */
    private $reservation;

    /** @var IAdditionsControlsBuilderFactory */
    private $additionsControlsBuilderFactory;

    /** @var AdditionsControlsBuilder|null */
    private $additionsControlsBuilder;

    /** @var bool */
    private $admin;

    /** @var ApplicationEntity[] */
    private $applications = [];

    /**
     * CartFormWrapper constructor.
     * @param bool $admin
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param CurrencyDao $currencyDao
     * @param CartManager $cartManager
     * @param ApplicationDao $applicationDao
     * @param InsuranceCompanyDao $insuranceCompanyDao
     * @param IAdditionsControlsBuilderFactory $additionsControlsBuilderFactory
     */
    public function __construct(
        bool $admin,
        FormWrapperDependencies $formWrapperDependencies,
        CurrencyDao $currencyDao,
        CartManager $cartManager,
        ApplicationDao $applicationDao,
        InsuranceCompanyDao $insuranceCompanyDao,
        IAdditionsControlsBuilderFactory $additionsControlsBuilderFactory
    ) {
        parent::__construct($formWrapperDependencies);
        $this->currencyDao = $currencyDao;
        $this->currency = $currencyDao->getDefaultCurrency();
        $this->cartManager = $cartManager;
        $this->applicationDao = $applicationDao;
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->additionsControlsBuilderFactory = $additionsControlsBuilderFactory;
        $this->admin = $admin;
    }

    /**
     * @return AdditionsControlsBuilder
     */
    public function getAdditionsControlsBuilder(): AdditionsControlsBuilder
    {
        if (null === $this->additionsControlsBuilder) {
            $builder = $this->additionsControlsBuilderFactory->create(
                $this->event,
                $this->currency
            )
                ->setAdmin($this->admin)
                ->setVisibilityPlace(AdditionEntity::VISIBLE_REGISTER)
                ->setVisiblePrice(true)
                ->setVisiblePriceTotal(true)
                ->setVisibleCountLeft(true);
            $this->additionsControlsBuilder = $builder;
        }

        return $this->additionsControlsBuilder;
    }

    public function setCart(CartEntity $cart): void
    {
        $event = $cart->getEvent();
        if (null === $event) {
            throw new RuntimeException('Missing event');
        }
        $this->cart = $cart;
        $this->event = $event;
        $this->early = $cart->getEarly();
        $this->substitute = $cart->getSubstitute();
        $this->applications = $cart->getApplications();
    }

    public function setReservation(ReservationEntity $reservation): void
    {
        $event = $reservation->getEvent();
        if (null === $event) {
            throw new RuntimeException('Missing event');
        }
        $this->reservation = $reservation;
        $this->event = $event;
        $this->applications = $reservation->getApplications();
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly(EarlyEntity $early): void
    {
        $this->early = $early;
        $wave = $early->getEarlyWave();
        if (null === $wave) {
            throw new RuntimeException('Missing wave in early');
        }
        $event = $wave->getEvent();
        if (null === $event) {
            throw new RuntimeException('Missing event in wave');
        }
        $this->event = $event;
        $this->substitute = null;
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent(EventEntity $event): void
    {
        $this->early = null;
        $this->event = $event;
        $this->substitute = null;
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function setSubstitute(SubstituteEntity $substitute): void
    {
        $event = $substitute->getEvent();
        if (null === $event) {
            throw new RuntimeException('Missing event');
        }
        $this->event = $event;
        $this->early = $substitute->getEarly();
        $this->substitute = $substitute;
    }

    /**
     * @param ApplicationEntity[] $applications
     */
    public function setApplications(array $applications): void
    {
        $this->applications = $applications;
    }

    protected function appendFormControls(Form $form): void
    {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $this->appendParentControls($form);
        $this->appendCommonControls($form);
        $this->appendApplicationsControls($form);
        $this->appendFinalControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->cart ? 'Form.Action.Save' : 'Form.Action.Register',
            [$this, 'registerClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null !== $this->early) {
            $form->setDefaults($this->early->getValueArray());
        }
        if (null !== $this->substitute) {
            $form->setDefaults($this->substitute->getValueArray());
        }
        if (null !== $this->cart) {
            $form->setDefaults($this->cart->getValueArray());
        }
        if (null !== $this->reservation) {
            $form->setDefaults($this->reservation->getValueArray());
        }
        foreach ($this->applications as $application) {
            $applicationId = (string)$application->getId();
            /** @var Container $applicationsContainer */
            $applicationsContainer = $form[self::CONTAINER_NAME_APPLICATIONS];
            /** @var Container $applicationItemContainer */
            $applicationItemContainer = $applicationsContainer[$applicationId];
            /** @var Container $applicationContainer */
            $applicationContainer = $applicationItemContainer[self::CONTAINER_NAME_APPLICATION];
            $applicationContainer->setDefaults($application->getValueArray(null, ['birthDate']));
            $birthDate = $application->getBirthDate();
            if (null !== $birthDate) {
                $applicationContainer->setDefaults(['birthDate' => $birthDate->format('d.m.Y')]);
            }
            $insuranceCompany = $application->getInsuranceCompany();
            if (null !== $insuranceCompany) {
                $applicationContainer->setDefaults(['insuranceCompanyId' => $insuranceCompany->getId()]);
            }
            /** @var Container $commonContainer */
            $commonContainer = $form[self::CONTAINER_NAME_COMMONS];
            $commonContainer->setDefaults($application->getValueArray());
            foreach ($application->getChoices() as $choice) {
                $option = $choice->getOption();
                if (null === $option) {
                    continue;
                }
                $addition = $option->getAddition();
                if (null === $addition) {
                    continue;
                }
                /** @var Container $additionsContainer */
                $additionsContainer = $applicationItemContainer[AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS];
                $additionsContainer->setDefaults(
                    [
                        $addition->getId() => $option->getId(),
                    ]
                );
            }
        }
    }

    /**
     * @param mixed[] $values
     * @return mixed[]
     */
    protected function preprocessValues(array $values): array
    {
        $index = 1;
        $applicationIds = [];
        foreach ($this->applications as $application) {
            $applicationIds[] = $application->getId();
        }
        foreach ($values[self::CONTAINER_NAME_APPLICATIONS] as $applicationId => $applicationValues) {
            if (count($this->applications) > 0 && !in_array($applicationId, $applicationIds, true)) {
                unset($values[self::CONTAINER_NAME_APPLICATIONS][$applicationId]);
            }
        }
        if (0 === count($values[self::CONTAINER_NAME_APPLICATIONS])) {
            throw new EmptyException("Error.Application.Empty");
        }
        $builder = $this->getAdditionsControlsBuilder();
        foreach ($values[self::CONTAINER_NAME_APPLICATIONS] as $applicationId => $applicationValues) {
            if (count($this->applications) > 0 && !in_array($applicationId, $applicationIds, true)) {
                unset($values[self::CONTAINER_NAME_APPLICATIONS][$applicationId]);
            }
            $applicationValues[self::CONTAINER_NAME_APPLICATION]['birthDate'] = DateTime::createFromFormat(
                'd.m.Y',
                $applicationValues[self::CONTAINER_NAME_APPLICATION]['birthDate']
            );
            if (false === $applicationValues[self::CONTAINER_NAME_APPLICATION]['birthDate']) {
                throw new FormControlException(
                    new InvalidInputException('Chybný formát data'),
                    ['birthDate']
                );
            }
            $builder->resetPreselectedOptions();
            if (null !== $this->reservation) {
                $application = $this->applicationDao->getApplication($applicationId);

                /** @var int[] $preselectedOptionIds */
                $preselectedOptionIds = [];
                if (null !== $application) {
                    foreach ($application->getChoices() as $choice) {
                        $option = $choice->getOption();
                        if (null === $option) {
                            continue;
                        }
                        $preselectedOptionIds[] = (int)$option->getId();
                    }
                }
                $builder->setPreselectedOptions($preselectedOptionIds);
            }
            try {
                $applicationValues = $builder->preprocessAdditionsValues($applicationValues, $index);
            } catch (FormControlException $e) {
                throw $e->prependControlPath($applicationId)
                    ->prependControlPath(self::CONTAINER_NAME_APPLICATIONS);
            }
            $values[self::CONTAINER_NAME_APPLICATIONS][$applicationId] = $applicationValues;
            $index++;
        }

        return $values;
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     * @throws Exception
     */
    protected function registerClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form) {
            return;
        }
        /** @var mixed[] $values */
        $values = $form->getValues('array');
        $values = $this->preprocessValues($values);
        if (null !== $this->cart) {
            $this->cartManager->editCartFromCartForm(
                $values,
                $this->event,
                $this->early,
                $this->substitute,
                $this->cart
            );
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Cart.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Application:', $this->event->getId());
        } else {
            $this->cartManager->createCartFromCartForm(
                $values,
                $this->event,
                $this->early,
                $this->substitute,
                $this->reservation
            );
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Cart.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Homepage:');
        }
    }

    protected function appendFinalControls(Form $form): void
    {
        $form->setCurrentGroup();
        $form->addHtml(
            'total',
            'Celková cena',
            Html::el('div', ['class' => 'price_total'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_currency']))
                ->addHtml($this->createRecalculateHtml())
        );
    }

    protected function appendParentControls(Form $form): void
    {
        $form->addGroup('Zákonný zástupce dětí')
            ->setOption(
                Form::OPTION_KEY_DESCRIPTION,
                "Mají-li děti různé zákonné zástupce, vyplňte pro každé dítě formulář samostatně."
            );
        $form->addText('firstName', 'Jméno', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $form->addText('lastName', 'Příjmení', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $form->addText('phone', 'Telefon', null, 13)
            ->setOption(
                $form::OPTION_KEY_DESCRIPTION,
                Html::el('div', ['class' => 'description control-description'])
                    ->addHtml(Html::el('div')->setText('Ve formátu +420123456789'))
                    ->addHtml(
                        Html::el('div')->setText('Pro případ onemocnění dítěte a poskytnutí informací')
                    )
            )
            ->setDefaultValue('+420')
            ->setRequired()
            ->addRule($form::PATTERN, '%label musí být ve formátu +420123456789', '[+]([0-9]){6,20}');
        $form->addText('email', 'Email')
            ->setRequired()
            ->addRule($form::EMAIL)
            ->setDefaultValue('@');
    }

    protected function appendCommonControls(Form $form): void
    {
        $form->addGroup('Bydliště dětí');
        $comons = $form->addContainer(self::CONTAINER_NAME_COMMONS);
        $comons->addText('address', 'Ulice č.p.', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $comons->addText('city', 'Město', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $comons->addText('zip', 'PSČ', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
    }

    protected function appendApplicationsControls(Form $form): void
    {
        $form->addGroup('Přihlášky');
        $multiplier = $form->addMultiplier(
            self::CONTAINER_NAME_APPLICATIONS,
            function (Container $applicationContainer) use ($form): void {
                /** @var Container $applicationsContainer */
                $applicationsContainer = $form[self::CONTAINER_NAME_APPLICATIONS];
                $applicationIndex = iterator_count($applicationsContainer->getComponents());
                $group = $form->addGroup()
                    ->setOption($form::OPTION_KEY_CLASS, 'price_subspace');
                $parentGroup = $form->getGroup('Přihlášky');
                if (null === $parentGroup) {
                    throw new RuntimeException('Missing parent gorup');
                }
                $count = (int)$parentGroup->getOption($form::OPTION_KEY_EMBED_NEXT, 0);
                $parentGroup->setOption($form::OPTION_KEY_EMBED_NEXT, $count + 1);
                $applicationContainer->setCurrentGroup($group);

                $this->appendApplicationControls($form, $applicationContainer);
                $this->appendAdditionsControls($applicationContainer, $applicationIndex);
            },
            $this->getApplicationCount(),
            $this->isApplicationCountFixed() ? $this->getApplicationCount() : null,
        );
        if (null === $this->substitute && null === $this->cart && null === $this->reservation) {
            $multiplier->addCreateButton('Přidat další přihlášku');
        }
        if (null === $this->cart) {
            $multiplier->addRemoveButton('Zrušit tuto přihlášku');
        }
    }

    protected function appendAdditionsControls(Container $applicationContainer, int $applicationIndex): void
    {
        $builder = $this->getAdditionsControlsBuilder();
        if (null !== $this->reservation) {
            $builder->setVisibleCountLeft(false);
            $builder->setPredisabledAdditionVisibilities([AdditionEntity::VISIBLE_RESERVATION]);
        }
        $builder->appendAdditionsControls($applicationContainer, $applicationIndex);
    }

    private function getApplicationCount(): int
    {
        if (null !== $this->cart) {
            return 0;
        }
        if (null !== $this->reservation) {
            return 0;
        }
        if (null !== $this->substitute) {
            return $this->substitute->getCount();
        }

        return 0;
    }

    private function isApplicationCountFixed(): bool
    {
        if (null !== $this->cart) {
            return false;
        }
        if (null !== $this->reservation) {
            return false;
        }
        if (null !== $this->substitute) {
            return false;
        }

        return true;
    }

    protected function appendApplicationControls(Form $form, Container $container): void
    {
        $applicationContainer = $container->addContainer(self::CONTAINER_NAME_APPLICATION);
        $applicationContainer->addText('firstName', 'Jméno', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $applicationContainer->addText('lastName', 'Příjmení', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $applicationContainer->addRadioList(
            'gender',
            'Pohlaví',
            [
                GenderEnum::MALE()->getValue() => 'Muž',
                GenderEnum::FEMALE()->getValue() => 'Žena',
            ]
        )
            ->setRequired();
        //TODO date input
        $applicationContainer->addText('birthDate', 'Datum narození')
            ->setRequired()
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Ve formátu dd.mm.rrrr')
            ->addRule($form::PATTERN, 'Vloženo chybné datum!', '[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}');
        $applicationContainer->addSelect(
            'insuranceCompanyId',
            'Zdravotní pojišťovna',
            $this->insuranceCompanyDao->getInsuranceCompanyList()
        )
            ->setRequired(true);
        $applicationContainer->addTextArea('friend', 'Umístění')
            ->setOption(
                Form::OPTION_KEY_DESCRIPTION,
                "S kým máte zájem umístit dítě do oddílu. Uveďte maximálně jedno jméno.
                Umístění můžeme garantovat pouze u té dvojice dětí,
                jejichž jména budou vzájemně uvedena na obou přihláškách.
                Vzhledem ke snaze o sestavení vyrovnaných oddílů nemůžeme zaručit společné umístění většího počtu dětí.
                U sourozenců uveďte, zda je chcete společně do oddílu."
            )
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, null, 256);
        $applicationContainer->addTextArea('info', 'Další informace')
            ->setOption(Form::OPTION_KEY_DESCRIPTION, "Fobie, stravovací návyky a podobně")
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, null, 512);
    }
}
