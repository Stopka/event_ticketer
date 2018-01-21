<?php

namespace App\Controls\Forms;

use App\Model\Exception\FormControlException;
use App\Model\Persistence\Attribute\IGender;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\Manager\CartManager;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Vodacek\Forms\Controls\DateInput;


class CartFormWrapper extends FormWrapper {
    use TRecalculateControl;

    const CONTAINER_NAME_APPLICATIONS = 'applications';
    const CONTAINER_NAME_APPLICATION = 'application';
    const CONTAINER_NAME_COMMONS = 'commons';

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

    /** @var  EarlyEntity */
    private $early;

    /** @var  EventEntity */
    private $event;

    /** @var  SubstituteEntity */
    private $substitute;

    /** @var  CartEntity */
    private $cart;

    /** @var ReservationEntity */
    private $reservation;

    /** @var IAdditionsControlsBuilderFactory */
    private $additionsControlsBuilderFactory;

    /** @var AdditionsControlsBuilder */
    private $additionsControlsBuilder;

    /**
     * CartFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param CurrencyDao $currencyDao
     * @param CartManager $cartManager
     * @param ApplicationDao $applicationDao
     * @param InsuranceCompanyDao $insuranceCompanyDao
     * @param IAdditionsControlsBuilderFactory $additionsControlsBuilderFactory
     */
    public function __construct(
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
                ->setAdmin($this->cart ? true : false)
                ->setVisibilityPlace(AdditionEntity::VISIBLE_REGISTER)
                ->setVisiblePrice(true)
                ->setVisiblePriceTotal(true)
                ->setVisibleCountLeft(true);
            $this->additionsControlsBuilder = $builder;
        }
        return $this->additionsControlsBuilder;
    }

    public function setCart(CartEntity $cart) {
        $this->cart = $cart;
        $this->event = $cart->getEvent();
        $this->early = $cart->getEarly();
        $this->substitute = $cart->getSubstitute();
    }

    public function setReservation(ReservationEntity $reservation) {
        $this->reservation = $reservation;
        $this->event = $reservation->getEvent();
    }

    /**
     * @param \App\Model\Persistence\Entity\EarlyEntity $early
     */
    public function setEarly(EarlyEntity $early) {
        $this->early = $early;
        $wave = $early->getEarlyWave();
        if (!$wave)
            return;
        $this->event = $wave->getEvent();
        $this->substitute = NULL;
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent(EventEntity $event) {
        $this->early = null;
        $this->event = $event;
        $this->substitute = NULL;
    }

    /**
     * @param \App\Model\Persistence\Entity\SubstituteEntity $substitute
     */
    public function setSubstitute(SubstituteEntity $substitute) {
        $this->event = $substitute->getEvent();
        $this->early = $substitute->getEarly();
        $this->substitute = $substitute;
    }

    protected function appendFormControls(Form $form) {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $this->appendParentControls($form);
        $this->appendCommonControls($form);
        $this->appendApplicationsControls($form);
        $this->appendFinalControls($form);
        $this->appendSubmitControls($form, $this->cart ? 'Form.Action.Save' : 'Form.Action.Register', [$this, 'registerClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if ($this->early) {
            $form->setDefaults($this->early->getValueArray());
        }
        if ($this->substitute) {
            $form->setDefaults($this->substitute->getValueArray());
        }
        if ($this->cart) {
            $form->setDefaults($this->cart->getValueArray());
            foreach ($this->cart->getApplications() as $application) {
                /** @var Container $applicationContainer */
                $applicationContainer = $form[self::CONTAINER_NAME_APPLICATIONS][$application->getId()][self::CONTAINER_NAME_APPLICATION];
                $applicationContainer->setDefaults($application->getValueArray());
                /** @var Container $commonContainer */
                $commonContainer = $form[self::CONTAINER_NAME_COMMONS];
                $commonContainer->setDefaults($application->getValueArray());
                foreach ($application->getChoices() as $choice) {
                    /** @var Container $additionsContainer */
                    $additionsContainer = $form[self::CONTAINER_NAME_APPLICATIONS][$application->getId()][AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS];
                    $additionsContainer->setDefaults([
                        $choice->getOption()->getAddition()->getId() => $choice->getOption()->getId()
                    ]);
                }
            }
        }
        if ($this->reservation) {
            $form->setDefaults($this->reservation->getValueArray());
            foreach ($this->reservation->getApplications() as $application) {
                /** @var Container $applicationContainer */
                $applicationContainer = $form[self::CONTAINER_NAME_APPLICATIONS][$application->getId()][self::CONTAINER_NAME_APPLICATION];
                $applicationContainer->setDefaults($application->getValueArray());
                /** @var Container $commonContainer */
                $commonContainer = $form[self::CONTAINER_NAME_COMMONS];
                $commonContainer->setDefaults($application->getValueArray());
                foreach ($application->getChoices() as $choice) {
                    /** @var Container $additionsContainer */
                    $additionsContainer = $form[self::CONTAINER_NAME_APPLICATIONS][$application->getId()][AdditionsControlsBuilder::CONTAINER_NAME_ADDITIONS];
                    $additionsContainer->setDefaults([
                        $choice->getOption()->getAddition()->getId() => $choice->getOption()->getId()
                    ]);
                }
            }
        }
    }

    protected function preprocessValues(array $values): array {
        $index = 1;
        foreach ($values[self::CONTAINER_NAME_APPLICATIONS] as $key => $applicationValues) {
            try {
                $applicationValues = $this->getAdditionsControlsBuilder()->preprocessAdditionsValues($applicationValues, $index);
            } catch (FormControlException $e) {
                throw $e->prependControlPath($key)->prependControlPath(self::CONTAINER_NAME_APPLICATIONS);
            }
            $values[self::CONTAINER_NAME_APPLICATIONS][$key] = $applicationValues;
            $index++;
        }
        return $values;
    }

    /**
     * @param SubmitButton $button
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    protected function registerClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessValues($values);
        if ($this->cart) {
            $this->cartManager->editCartFromCartForm($values, $this->event, $this->early, $this->substitute, $this->cart);
            $this->getPresenter()->flashTranslatedMessage('Form.Cart.Message.Edit.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Application:', $this->event->getId());
        } else {
            $this->cartManager->createCartFromCartForm($values, $this->event, $this->early, $this->substitute, $this->reservation);
            $this->getPresenter()->flashTranslatedMessage('Form.Cart.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Homepage:');
        }
    }

    protected function appendFinalControls(Form $form) {
        $form->setCurrentGroup();
        $form->addHtml('total', 'Celková cena',
            Html::el('div', ['class' => 'price_total'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_currency']))
                ->addHtml($this->createRecalculateHtml())
        );
    }

    protected function appendParentControls(Form $form) {
        $form->addGroup('Zákonný zástupce dětí')
            ->setOption(Form::OPTION_KEY_DESCRIPTION, "Mají-li děti různé zákonné zástupce, vyplňte pro každé dítě formulář samostatně.");
        $form->addText('firstName', 'Jméno', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('lastName', 'Příjmení', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('phone', 'Telefon', NULL, 13)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Ve formátu +420123456789')
            ->setDefaultValue('+420')
            ->setRequired()
            ->addRule($form::PATTERN, '%label musí být ve formátu +420123456789', '[+]([0-9]){6,20}');
        $form->addText('email', 'Email')
            ->setRequired()
            ->addRule($form::EMAIL)
            ->setDefaultValue('@');
    }

    protected function appendCommonControls(Form $form) {
        $form->addGroup('Bydliště dětí');
        $comons = $form->addContainer(self::CONTAINER_NAME_COMMONS);
        $comons->addText('street', 'Ulice č.p.', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $comons->addText('city', 'Město', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $comons->addText('zip', 'PSČ', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
    }

    protected function appendApplicationsControls(Form $form) {
        $form->addGroup('Přihlášky');
        $removeEvent = [$this, 'removeApplication'];
        $count_left = $this->event->getCapacityLeft($this->applicationDao->countIssuedApplications($this->event));
        if (!$this->substitute && !$this->cart && !$this->reservation) {
            $add_button = $form->addSubmit('add', 'Přidat další přihlášku')
                ->setOption($form::OPTION_KEY_DESCRIPTION,
                    Html::el('span', [
                        'class' => 'description control-description countLeft'
                    ])
                        ->setText("Zbývá $count_left přihlášek")
                )
                ->setValidationScope(FALSE);
            /** @noinspection PhpUndefinedFieldInspection */
            $add_button->getControlPrototype()->class = 'ajax';
            $add_button->onClick[] = [$this, 'addApplication'];
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $form->addDynamic(self::CONTAINER_NAME_APPLICATIONS, function (Container $application) use ($removeEvent, $form) {
            /** @var \Kdyby\Replicator\Container $applicationsContainer */
            $applicationsContainer = $form[self::CONTAINER_NAME_APPLICATIONS];
            $applicationIndex = iterator_count($applicationsContainer->getComponents());
            $group = $form->addGroup()
                ->setOption($form::OPTION_KEY_CLASS, 'price_subspace');
            $parent_group = $form->getGroup('Přihlášky');
            $count = $parent_group->getOption($form::OPTION_KEY_EMBED_NEXT);
            $parent_group->setOption($form::OPTION_KEY_EMBED_NEXT, $count ? $count + 1 : 1);
            $application->setCurrentGroup($group);

            $this->appendApplicationControls($form, $application);
            $this->getAdditionsControlsBuilder()->appendAdditionsControls($application, $applicationIndex);

            if (!$this->cart) {
                $remove_button = $application->addSubmit('remove', 'Zrušit tuto přihlášku')
                    ->setValidationScope(FALSE); # disables validation
                $remove_button->onClick[] = $removeEvent;
                /** @noinspection PhpUndefinedFieldInspection */
                $remove_button->getControlPrototype()->class = 'ajax';
            }
        }, $this->getApplicationCount(), $this->isApplicationCountFixed());
    }

    private function getApplicationCount() {
        if ($this->cart) {
            return 0;
        }
        if ($this->reservation) {
            return 0;
        }
        if ($this->substitute) {
            return $this->substitute->getCount();
        }
        return 1;
    }

    private function isApplicationCountFixed() {
        if ($this->cart) {
            return false;
        }
        if ($this->reservation) {
            return false;
        }
        if ($this->substitute) {
            return false;
        }
        return true;
    }

    protected function appendApplicationControls(Form $form, Container $container) {
        $applicationContainer = $container->addContainer(self::CONTAINER_NAME_APPLICATION);
        $applicationContainer->addText('firstName', 'Jméno', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $applicationContainer->addText('lastName', 'Příjmení', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $applicationContainer->addRadioList('gender', 'Pohlaví', [
            IGender::MALE => 'Muž',
            IGender::FEMALE => 'Žena',
        ])
            ->setRequired();
        /** @noinspection PhpUndefinedMethodInspection */
        $applicationContainer->addDate('birthDate', 'Datum narození', DateInput::TYPE_DATE)
            ->setRequired()
            ->addRule($form::VALID, 'Vloženo chybné datum!');
        $applicationContainer->addSelect('insuranceCompanyId', 'Zdravotní pojišťovna',
            $this->insuranceCompanyDao->getInsuranceCompanyList())
            ->setRequired(true);
        $applicationContainer->addTextArea('friend', 'Umístění')
            ->setOption(Form::OPTION_KEY_DESCRIPTION, "S kým máte zájem umístit dítě do oddílu. Uveďte maximálně jedno jméno. Umístění můžeme garantovat pouze u té dvojice dětí, jejichž jména budou vzájemně uvedena na obou přihláškách. Vzhledem ke snaze o sestavení vyrovnaných oddílů nemůžeme zaručit společné umístění většího počtu dětí. U sourozenců uveďte, zda je chcete společně do oddílu.")
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, null, 256);
        $applicationContainer->addTextArea('info', 'Další informace')
            ->setOption(Form::OPTION_KEY_DESCRIPTION, "Fobie, stravovací návyky a podobně")
            ->setRequired(false)
            ->addRule($form::MAX_LENGTH, null, 512);
    }


    public function addApplication(SubmitButton $button) {
        $form = $button->getForm();
        /** @var \Kdyby\Replicator\Container $applicationsContainer */
        $applicationsContainer = $form[self::CONTAINER_NAME_APPLICATIONS];
        $applicationsContainer->createOne();
        $this->redrawControl('form');
    }

    public function removeApplication(SubmitButton $button) {
        /** @var Container $applicationContainer */
        $applicationContainer = $button->getParent();
        /** @var \Kdyby\Replicator\Container $applicationsContainer */
        $applicationsContainer = $applicationContainer->getParent();
        $applicationsContainer->remove($applicationContainer, TRUE);
        $this->redrawControl('form');
    }

}
