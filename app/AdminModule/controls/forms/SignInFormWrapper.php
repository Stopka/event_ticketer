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
use Nette\Security\User;

class SignInFormWrapper extends FormWrapper {

    /** @var  User */
    private $user;

    public function __construct(User $user) {
        parent::__construct();
        $this->user = $user;
    }


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
        $user = $this->user;
        try{
            $user->setExpiration('+ 20 minutes', TRUE);
            $user->login($values['username'],$values['password']);
        }catch (AuthenticationException $e){
            throw new \App\Model\Exception\AuthenticationException($e->getMessage(),$e);
        }
        $this->redirect('this');
    }
}