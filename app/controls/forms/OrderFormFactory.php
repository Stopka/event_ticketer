<?php

namespace App\Controls\Forms;

use Nette\Application\UI\Form;


class OrderFormFactory extends FormFactory {

    /**
     * @return Form
     */
    public function create($callback) {
        $form = parent::create($callback);
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
        $form->addRadioList('transport','Doprava',[
            1=>'Individuální',
            2=>'Autobusem'
        ]);
        $form->addRadioList('tričko','Táborové tričko',[
            0=>'Žádné',
            1=>'Tričko S',
            2=>'Tričko M',
            3=>'Tričko L',
            4=>'Tričko XL'
        ]);

        return $form;
    }

}
