<?php

namespace App\Controls\Forms;

use App\Model\Entities\ApplicationEntity;
use App\Model\Entities\CurrencyEntity;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OrderEntity;
use App\Model\Entities\SubstituteEntity;
use App\Model\Facades\ApplicationFacade;
use App\Model\Facades\CurrencyFacade;
use App\Model\Facades\OrderFacade;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Vodacek\Forms\Controls\DateInput;


class OrderFormWrapper extends FormWrapper {
    use AppendAdditionsControls;

    /** @var  OrderFacade */
    private $orderFacade;

    /** @var  CurrencyFacade */
    private $currencyFacade;

    /** @var  ApplicationFacade */
    private $applicationFacade;

    /** @var  CurrencyEntity */
    protected $currency;

    /** @var  EarlyEntity */
    private $early;

    /** @var  EventEntity */
    private $event;

    /** @var  SubstituteEntity */
    private $substitute;

    /** @var  OrderEntity */
    private $order;

    public function __construct(CurrencyFacade $currencyFacade, OrderFacade $orderFacade, ApplicationFacade $applicationFacade) {
        parent::__construct();
        $this->currencyFacade = $currencyFacade;
        $this->currency = $currencyFacade->getDefaultCurrency();
        $this->orderFacade = $orderFacade;
        $this->applicationFacade = $applicationFacade;
        $this->setTemplate(__DIR__ . '/OrderFormWrapper.latte');
    }

    protected function getEvent() {
        return $this->event;
    }

    protected function getCurrency() {
        return $this->currency;
    }

    protected function getApplicationFacade() {
        return $this->applicationFacade;
    }

    public function setOrder(OrderEntity $order){
        $this->order = $order;
        $this->event = $order->getEvent();
        $this->early = $order->getEarly();
        $this->substitute = $order->getSubstitute();
    }

    public function isAdmin() {
        return $this->order?true:false;
    }


    /**
     * @param EarlyEntity $early
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
     * @param SubstituteEntity $substitute
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
        $this->appendChildrenControls($form);
        $this->appendFinalControls($form);
        $this->appendSubmitControls($form, $this->order?'Uložit':'Rezervovat', [$this, 'registerClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if ($this->early) {
            $form->setDefaults($this->early->getValueArray());
        }
        if ($this->substitute) {
            $form->setDefaults($this->substitute->getValueArray());
        }
        if($this->order){
            $form->setDefaults($this->order->getValueArray());
            foreach ($this->order->getApplications() as $application){
                $form['children'][$application->getId()]['child']->setDefaults($application->getValueArray());
                $form['commons']->setDefaults($application->getValueArray());
                foreach ($application->getChoices() as $choice){
                    $form['children'][$application->getId()]['addittions']->setDefaults([
                        $choice->getOption()->getAddition()->getId() => $choice->getOption()->getId()
                    ]);
                }
            }
        }
    }

    /**
     * @param SubmitButton $button
     */
    protected function registerClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        if($this->order) {
            $this->orderFacade->editOrderFromOrderForm($values, $this->event, $this->early, $this->substitute, $this->order);
            $this->getPresenter()->flashMessage('Objednávka byla uložena.', 'success');
            $this->getPresenter()->redirect('Application:',$this->event->getId());
        }else{
            $this->orderFacade->createOrderFromOrderForm($values, $this->event, $this->early, $this->substitute);
            $this->getPresenter()->flashMessage('Registarce byla vytvořena. Přihlášky byly odeslány emailem.', 'success');
            $this->getPresenter()->redirect('Homepage:');
        }
    }

    protected function appendFinalControls(Form $form) {
        $form->setCurrentGroup();
        $form->addHtml('total', 'Celková cena',
            Html::el('div', ['class' => 'price_total'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_currency']))->addHtml($this->createRecalculateHtml())
        );
    }

    protected function appendParentControls(Form $form) {
        $form->addGroup('Rodič');
        $form->addText('firstName', 'Jméno', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('lastName', 'Příjmení', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('phone', 'Telefon', NULL, 13)
            ->setOption('description', 'Ve formátu +420123456789')
            ->setRequired()
            ->addRule($form::PATTERN, '%label musí být ve formátu +420123456789', '[+]([0-9]){6,20}');
        $form->addText('email', 'Email')
            ->setRequired()
            ->addRule($form::EMAIL);
    }

    protected function appendCommonControls(Form $form) {
        $form->addGroup('Bydliště dětí');
        $comons = $form->addContainer('commons');
        $comons->addText('street', 'Ulice', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $comons->addText('address', 'Číslo popisné', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $comons->addText('city', 'Město', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $comons->addText('zip', 'PSČ', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
    }

    protected function appendChildrenControls(Form $form) {
        $form->addGroup('Přihlášky');
        $removeEvent = [$this, 'removeChild'];
        $count_left = $this->event->getCapacityLeft($this->applicationFacade->countIssuedApplications($this->event));
        if(!$this->substitute&&!$this->order) {
            $add_button = $form->addSubmit('add', 'Přidat další přihlášku')
                ->setOption('description', "Zbývá $count_left přihlášek")
                ->setValidationScope(FALSE);
            $add_button->getControlPrototype()->class = 'ajax';
            $add_button->onClick[] = [$this, 'addChild'];
        }
        $children = $form->addDynamic('children', function (Container $child) use ($removeEvent, $form) {
            $group = $form->addGroup()
                ->setOption('class', 'price_subspace');
            $parent_group = $form->getGroup('Přihlášky');
            $count = $parent_group->getOption('embedNext');
            $parent_group->setOption('embedNext', $count ? $count + 1 : 1);
            $child->setCurrentGroup($group);

            $this->appendChildControls($form, $child);
            $this->appendAdditionsControls($form, $child);

            if(!$this->order) {
                $remove_button = $child->addSubmit('remove', 'Zrušit tuto přihlášku')
                    ->setValidationScope(FALSE); # disables validation
                $remove_button->onClick[] = $removeEvent;
                $remove_button->getControlPrototype()->class = 'ajax';
            }
        }, $this->getApplicationCount(), $this->isApplicationCountFixed());
    }

    private function getApplicationCount(){
        if($this->order){
            return 0;
        }
        if($this->substitute){
            return $this->substitute->getCount();
        }
        return 1;
    }

    private function isApplicationCountFixed(){
        if($this->order){
            return false;
        }
        if($this->substitute){
            return false;
        }
        return true;
    }

    protected function appendChildControls(Form $form, Container $container) {
        $child = $container->addContainer('child');
        $child->addText('firstName', 'Jméno', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $child->addText('lastName', 'Příjmení', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $child->addRadioList('gender', 'Pohlaví', [
            ApplicationEntity::GENDER_MALE => 'Muž',
            ApplicationEntity::GENDER_FEMALE => 'Žena',
        ])
            ->setRequired();
        $child->addDate('birthDate', 'Datum narození', DateInput::TYPE_DATE)
            ->setRequired()
            ->addRule(form::VALID, 'Entered date is not valid!');
        $child->addText('birthCode', 'Kód rodného čísla', NULL, 255)
            ->setOption('description', 'Část rodného čísla za lomítkem')
            ->setRequired()
            ->addRule($form::PATTERN, '%label musí být ve formátu čtyřmístného čísla', '[0-9]{4}');
    }



    public function addChild(SubmitButton $button) {
        $form = $button->getForm();
        $form['children']->createOne();
        $this->redrawControl('form');
    }

    public function removeChild(SubmitButton $button) {
        $child = $button->getParent();
        $children = $child->getParent();
        $children->remove($child, TRUE);
        $this->redrawControl('form');
    }

}
