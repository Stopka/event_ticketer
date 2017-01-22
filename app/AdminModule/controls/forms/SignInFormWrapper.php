<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\AuthenticationException;

class SignInFormWrapper extends FormWrapper {

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->addText('username','Uživatelské jméno',null,255)
            ->setAttribute("autocomplete","off")
            ->setRequired();
        $form->addPassword('password','Heslo', null, 255)
            ->setAttribute("autocomplete","off")
            ->setRequired();
        $this->appendSubmitControls($form,'Přihlásit',[$this,'loginClicked']);
    }

    public function loginClicked(SubmitButton $button){
        $values = $button->getForm()->getValues();
        $user = $this->getPresenter()->getUser();
        try{
            $user->setExpiration('+ 20 minutes', TRUE);
            $user->login($values['username'],$values['password']);
        }catch (AuthenticationException $e){
            throw new \App\Model\Exceptions\AuthenticationException($e->getMessage(),$e);
        }
        $this->redirect('this');
    }
}