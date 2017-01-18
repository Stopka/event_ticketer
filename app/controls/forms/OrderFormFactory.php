<?php

namespace App\Controls\Forms;

use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;


class OrderFormFactory extends FormFactory {

    /**
     * @return Form
     */
    public function create($callback) {
        $form = parent::create($callback);
        $this->createParentControls($form);
        $this->createCommonControls($form);
        $this->createChildrenControls($form);
        $this->createSubmitControls($form);
        return $form;
    }

    protected function createSubmitControls(Form $form){
        $form->setCurrentGroup();
        $form->addSubmit('submit','Rezervovat');
    }

    protected function createParentControls(Form $form){
        $form->addGroup('Rodič');
        $form->addText('firstName','Jméno',NULL,255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('lastName','Příjmení',NULL,255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('phone','Telefon',NULL,13)
            ->setOption('description','Ve formátu +420123456789')
            ->setRequired()
            ->addRule($form::PATTERN,'%label musí být ve formátu +420123456789','[+]([0-9]){6,20}');
        $form->addText('email','Email')
            ->addRule($form::EMAIL);
    }

    protected function createCommonControls(Form $form){
        $form->addGroup('Bydliště dětí');
        $form->addText('address','Adresa',NULL,255)
            ->setOption('description','Ulice a číslo popisné')
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('city','Město',NULL,255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('zip','PSČ',NULL,255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
    }

    protected function createChildrenControls(Form $form){
        $form->addGroup('Přihlášky');
        $add_button=$form->addSubmit('add', 'Přidat přihlášku')
            ->setValidationScope(FALSE);
        $add_button->getControlPrototype()->class='ajax';
        $add_button->onClick[] = [$this,'addChild'];
        $removeEvent = [$this,'removeChild'];
        $children=$form->addDynamic('children', function (Container $child) use($removeEvent,$form) {
            $group=$form->addGroup();
            $parent_group=$form->getGroup('Přihlášky');
            $count=$parent_group->getOption('embedNext');
            $parent_group->setOption('embedNext',$count?$count+1:1);
            $child->setCurrentGroup($group);

            $this->createChildControls($form,$child);
            $this->createAdditionsControls($form,$child);


            $remove_button=$child->addSubmit('remove', 'Zrušit přihlášku')
                ->setValidationScope(FALSE); # disables validation
            $remove_button->onClick[] = $removeEvent;
            $remove_button->getControlPrototype()->class='ajax';
        }, 0);
    }

    protected function createChildControls(Form $form, Container $container){
        $form->addGroup("Dítě");
        $child = $container->addContainer('child');
        $child->addText('firstName','Jméno',NULL,255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $child->addText('lastName','Příjmení',NULL,255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $child->addText('birthDate','Datum narození',NULL,255)
            ->setRequired();
        $child->addText('birthCode','Kód rodného čísla',NULL,255)
            ->setOption('description','Část rodného čísla za lomítkem')
            ->setRequired()
            ->addRule($form::PATTERN,'%label musí být ve formátu čtyřmístného čísla','[0-9]{4}');
    }

    protected function createAdditionsControls(Form $form, Container $container){
        $form->addGroup("Položky");
        $addittions = $container->addContainer('addittions');
        $addittions->addRadioList('transport','Doprava',[
            1=>'Individuální',
            2=>'Autobusem'
        ]);
        $addittions->addRadioList('tričko','Táborové tričko',[
            0=>'Žádné',
            1=>'Tričko S',
            2=>'Tričko M',
            3=>'Tričko L',
            4=>'Tričko XL'
        ]);
    }

    public function addChild(SubmitButton $button){
        $form = $button->getForm();
        $form['children']->createOne();
    }

    public function removeChoice(SubmitButton $button){
        $child = $button->getParent();
        $children = $child->getParent();
        $children->remove($child, TRUE);
    }

}
