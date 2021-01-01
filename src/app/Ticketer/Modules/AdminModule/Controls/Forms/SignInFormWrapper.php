<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Exception;
use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Managers\AdministratorManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\AuthenticationException as NetteAuthenticationException;
use Nette\Security\User;
use Ticketer\Model\Exceptions\AuthenticationException;

class SignInFormWrapper extends FormWrapper
{

    private User $user;

    private AdministratorManager $administratorManager;

    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        User $user,
        AdministratorManager $administratorManager
    ) {
        parent::__construct($formWrapperDependencies);
        $this->user = $user;
        $this->administratorManager = $administratorManager;
    }


    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $form->addText('username', 'Attribute.Person.Username', null, 255)
            ->setHtmlAttribute("autocomplete", "off")
            ->setRequired();
        $form->addPassword('password', 'Attribute.Person.Password', null, 255)
            ->setHtmlAttribute("autocomplete", "off")
            ->setRequired();
        $this->appendSubmitControls($form, 'Form.Action.SignIn', [$this, 'loginClicked']);
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     * @throws Exception
     */
    public function loginClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $user = $this->user;
        try {
            $user->setExpiration('+ 20 minutes');
            $user->login($values['username'], $values['password']);
            $this->getPresenter()->flashTranslatedMessage(
                "Form.SignIn.Message.Success",
                FlashMessageTypeEnum::SUCCESS()
            );
        } catch (NetteAuthenticationException $e) {
            $created = $this->administratorManager->checkFirstAdministrator($values['username'], $values['password']);
            if (null !== $created) {
                $this->getPresenter()->flashTranslatedMessage(
                    "Form.SignIn.Message.AdministratorCreated",
                    FlashMessageTypeEnum::INFO()
                );
                $this->redirect('this');
            }
            throw new AuthenticationException(
                "Error.Authentication.InvalidCredentials",
                null,
                [],
                0,
                $e
            );
        }
        $this->redirect('this');
    }
}
