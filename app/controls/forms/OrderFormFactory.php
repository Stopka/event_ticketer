<?php

namespace App\Controls\Forms;

use App\Model\Entities\AdditionEntity;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OptionEntity;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;


class OrderFormFactory extends FormFactory {

    /** @var  EarlyEntity */
    private $early;

    /** @var  EventEntity */
    private $event;

    /**
     * @param EarlyEntity $early
     */
    public function setEarly($early) {
        $this->early = $early;
        $wave = $early->getEarlyWave();
        if (!$wave)
            return;
        $this->event = $wave->getEvent();
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent($event) {
        $this->early = null;
        $this->event = $event;
    }

    /**
     * @return Form
     */
    public function create() {
        $form = parent::create();
        $this->createParentControls($form);
        $this->createCommonControls($form);
        $this->createChildrenControls($form);
        $this->createSubmitControls($form);
        return $form;
    }

    protected function createSubmitControls(Form $form) {
        $form->setCurrentGroup();
        $form->addSubmit('submit', 'Rezervovat');
    }

    protected function createParentControls(Form $form) {
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

    protected function createCommonControls(Form $form) {
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

    protected function createChildrenControls(Form $form) {
        $form->addGroup('Přihlášky');
        $removeEvent = [$this, 'removeChild'];
        $add_button = $form->addSubmit('add', 'Přidat přihlášku')
            ->setValidationScope(FALSE);
        $add_button->getControlPrototype()->class = 'ajax';
        $add_button->onClick[] = [$this, 'addChild'];
        $children = $form->addDynamic('children', function (Container $child) use ($removeEvent, $form) {
            $group = $form->addGroup();
            $group->setOption('embedNext', 2);
            $parent_group = $form->getGroup('Přihlášky');
            $count = $parent_group->getOption('embedNext');
            $parent_group->setOption('embedNext', $count ? $count + 1 : 1);
            $child->setCurrentGroup($group);

            $this->createChildControls($form, $child);
            $this->createAdditionsControls($form, $child);


            $remove_button = $child->addSubmit('remove', 'Zrušit přihlášku')
                ->setValidationScope(FALSE); # disables validation
            $remove_button->onClick[] = $removeEvent;
            $remove_button->getControlPrototype()->class = 'ajax';
        }, 0);
    }

    protected function createChildControls(Form $form, Container $container) {
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

    protected function createAdditionsControls(Form $form, Container $container) {
        $subcontainer = $container->addContainer('addittions');
        foreach ($this->event->getAdditions() as $addition) {
            $this->createAdditionContols($subcontainer, $addition);
        }
    }

    protected function createAdditionContols(Container $container, AdditionEntity $addition) {
        $options = $this->createAdditionOptions($addition);
        if(!count($options)){
            return;
        }
        if($addition->getMaximum()>1&&count($options)>1){
            $control = $container->addCheckboxList($addition->getId(), $addition->getName(), $options)
                ->setRequired($addition->getMinimum()==0);
        }else{
            $control = $container->addRadioList($addition->getId(), $addition->getName(), $options)
                ->setRequired();
            if(count($options)==1) {
                $keys = array_keys($options);
                $key = array_pop($keys);
                $control->setDefaultValue($key);
            }
        }
    }

    /**
     * @param AdditionEntity $addition
     * @return array
     */
    protected function createAdditionOptions(AdditionEntity $addition){
        $result = [];
        foreach ($addition->getOptions() as $option){
            $result[$option->getId()]=$this->createOptionLabel($option);
        }
        return $result;
    }

    protected function createOptionLabel(OptionEntity $option){
        $price = $option->getPrice();
        //TODO výběr ceny v měně
        $amount = $price->getPriceAmounts()[0];
        return $option->getName().' '.$amount->getAmount().$amount->getCurrency()->getSymbol();
    }

    public function addChild(SubmitButton $button) {
        $form = $button->getForm();
        $form['children']->createOne();
    }

    public function removeChild(SubmitButton $button) {
        $child = $button->getParent();
        $children = $child->getParent();
        $children->remove($child, TRUE);
    }

}
