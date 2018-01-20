<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Controls\Forms\FormWrapperDependencies;
use App\Model\Persistence\Manager\AdministratorManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class SignInFormWrapper extends FormWrapper {

    /** @var  User */
    private $user;

    /** @var AdministratorManager */
    private $administratorManager;

    public function __construct(FormWrapperDependencies $formWrapperDependencies, User $user, AdministratorManager $administratorManager) {
        parent::__construct($formWrapperDependencies);
        $this->user = $user;
        $this->administratorManager = $administratorManager;
    }


    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->addText('username','Attribute.Person.Username',null,255)
            ->setAttribute("autocomplete","off")
            ->setRequired();
        $form->addPassword('password','Attribute.Person.Password', null, 255)
            ->setAttribute("autocomplete","off")
            ->setRequired();
        $this->appendSubmitControls($form,'Form.Action.SignIn',[$this,'loginClicked']);
    }

    /**
     * @param SubmitButton $button
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    public function loginClicked(SubmitButton $button){
        $values = $button->getForm()->getValues();
        $user = $this->user;
        try{
            $user->setExpiration('+ 20 minutes', TRUE);
            $user->login($values['username'],$values['password']);
            $this->getPresenter()->flashTranslatedMessage("Form.SignIn.Message.Success",self::FLASH_MESSAGE_TYPE_SUCCESS);
        }catch (AuthenticationException $e){
            $created = $this->administratorManager->checkFirstAdministrator($values['username'], $values['password']);
            if ($created) {
                $this->getPresenter()->flashTranslatedMessage("Form.SignIn.Message.AdministratorCreated", self::FLASH_MESSAGE_TYPE_INFO);
                return;
            }
            throw new \App\Model\Exception\AuthenticationException("Error.Authentication.InvalidCredentials", null, [], 0, $e);
        }
        $this->redirect('this');
    }
}