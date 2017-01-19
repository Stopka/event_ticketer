<?php

namespace App\Controls\Forms;

use App\Model\Entities\AdditionEntity;
use App\Model\Entities\CurrencyEntity;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OptionEntity;
use App\Model\Facades\CurrencyFacade;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Stopka\NetteFormRenderer\HtmlFormComponent;


class OrderFormWrapper extends FormWrapper {

    /** @var  CurrencyFacade */
    private $currencyFacade;

    /** @var  CurrencyEntity */
    protected $currency;

    /** @var  EarlyEntity */
    private $early;

    /** @var  EventEntity */
    private $event;

    public function __construct(CurrencyFacade $currencyFacade) {
        parent::__construct();
        $this->currencyFacade = $currencyFacade;
        $this->currency = $currencyFacade->getDefaultCurrency();
        $this->setTemplate(__DIR__ . '/OrderFormWrapper.latte');
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
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent(EventEntity $event) {
        $this->early = null;
        $this->event = $event;
    }

    protected function appendFormControls(Form $form) {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $this->appendParentControls($form);
        $this->appendCommonControls($form);
        $this->appendChildrenControls($form);
        $this->appendFinalControls($form);
        $this->appendSubmitControls($form, 'Rezervovat');
    }

    protected function appendFinalControls(Form $form) {
        $form->setCurrentGroup();
        $form->addHtml('total', 'Celková cena',
            Html::el('div', ['class' => 'price_total'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_curreny']))
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
        $form->addText('address', 'Adresa', NULL, 255)
            ->setOption('description', 'Ulice a číslo popisné')
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('city', 'Město', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('zip', 'PSČ', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
    }

    protected function appendChildrenControls(Form $form) {
        $form->addGroup('Přihlášky');
        $removeEvent = [$this, 'removeChild'];
        $add_button = $form->addSubmit('add', 'Přidat přihlášku')
            ->setValidationScope(FALSE);
        $add_button->getControlPrototype()->class = 'ajax';
        $add_button->onClick[] = [$this, 'addChild'];
        $children = $form->addDynamic('children', function (Container $child) use ($removeEvent, $form) {
            $group = $form->addGroup()
                ->setOption('class', 'price_subspace');
            $parent_group = $form->getGroup('Přihlášky');
            $count = $parent_group->getOption('embedNext');
            $parent_group->setOption('embedNext', $count ? $count + 1 : 1);
            $child->setCurrentGroup($group);

            $this->appendChildControls($form, $child);
            $this->appendAdditionsControls($form, $child);


            $remove_button = $child->addSubmit('remove', 'Zrušit přihlášku')
                ->setValidationScope(FALSE); # disables validation
            $remove_button->onClick[] = $removeEvent;
            $remove_button->getControlPrototype()->class = 'ajax';
        }, 0);
    }

    protected function appendChildControls(Form $form, Container $container) {
        $child = $container->addContainer('child');
        $child->addText('firstName', 'Jméno', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $child->addText('lastName', 'Příjmení', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $child->addText('birthDate', 'Datum narození', NULL, 255)
            ->setRequired();
        $child->addText('birthCode', 'Kód rodného čísla', NULL, 255)
            ->setOption('description', 'Část rodného čísla za lomítkem')
            ->setRequired()
            ->addRule($form::PATTERN, '%label musí být ve formátu čtyřmístného čísla', '[0-9]{4}');
    }

    protected function appendAdditionsControls(Form $form, Container $container) {
        $subcontainer = $container->addContainer('addittions');
        foreach ($this->event->getAdditions() as $addition) {
            $this->appendAdditionContols($subcontainer, $addition);
        }
        $subcontainer['total'] = new HtmlFormComponent('Celkem za přihlášku',
            Html::el('div', ['class' => 'price_subtotal'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_curreny']))
        );
    }

    protected function appendAdditionContols(Container $container, AdditionEntity $addition) {
        $prices = $this->createAdditionPrices($addition);
        $options = $this->createAdditionOptions($addition, $prices);
        if (!count($options)) {
            return;
        }
        if ($addition->getMaximum() > 1 && count($options) > 1) {
            $control = $container->addCheckboxList($addition->getId(), $addition->getName(), $options)
                ->setRequired($addition->getMinimum() == 0)
                ->setTranslator();
        } else {
            $control = $container->addRadioList($addition->getId(), $addition->getName(), $options)
                ->setRequired()
                ->setTranslator();
            if (count($options) == 1) {
                $keys = array_keys($options);
                $key = array_pop($keys);
                $control->setDefaultValue($key);
            }
        }
        $control->getControlPrototype()
            ->addClass('price_item')
            ->setAttribute('data-price-value', json_encode($prices));
    }

    /**
     * @param AdditionEntity $addition
     * @return array
     */
    protected function createAdditionPrices(AdditionEntity $addition) {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $amount = $option->getPrice()->getPriceAmountByCurrency($this->currency);
            $result[$option->getId()] = [
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency()->getSymbol()
            ];
        }
        return $result;
    }

    /**
     * @param AdditionEntity $addition
     * @param $prices array
     * @return array id=>Html
     */
    protected function createAdditionOptions(AdditionEntity $addition, $prices) {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $result[$option->getId()] = $this->createOptionLabel($option, $prices);
        }
        return $result;
    }

    /**
     * @param OptionEntity $option
     * @param $prices array
     * @return string
     */
    protected function createOptionLabel(OptionEntity $option, $prices) {
        $result = Html::el();
        if($option->getName()) {
            $result->addHtml(
                Html::el('span', ['class' => 'name'])
                    ->setText($option->getName())
            );
        }
        if (isset($prices[$option->getId()])) {
            $price = $prices[$option->getId()];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        return $result;
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
