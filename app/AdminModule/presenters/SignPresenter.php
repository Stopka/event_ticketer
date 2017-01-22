<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Controls\Forms\ISignInFormWrapperFactory;
use Nette;


class SignPresenter extends BasePresenter {
    /**
     * @var ISignInFormWrapperFactory
     * @inject
     */
    public $signInFromWrapperFactory;

    /**
     * @var string
     * @persistent
     */
    public $backlink;

    protected function checkUser() {

    }

    public function actionIn(){
        if($this->getUser()->isLoggedIn()){
            $this->redirect('Homepage:');
        }
    }


    public function actionOut() {
        $this->getUser()->logout();
        $this->redirect('Homepage:');
    }

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        return $this->signInFromWrapperFactory->create();
    }

}
