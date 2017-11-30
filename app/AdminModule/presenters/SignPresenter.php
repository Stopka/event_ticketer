<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Controls\Forms\ISignInFormWrapperFactory;
use Nette;


class SignPresenter extends BasePresenter {
    /** @var ISignInFormWrapperFactory */
    public $signInFromWrapperFactory;

    /**
     * @var string
     * @persistent
     */
    public $backlink;

    /**
     * SignPresenter constructor.
     * @param ISignInFormWrapperFactory $signInFormWrapperFactory
     */
    public function __construct(ISignInFormWrapperFactory $signInFormWrapperFactory) {
        parent::__construct();
        $this->signInFromWrapperFactory = $signInFormWrapperFactory;
    }


    protected function checkUser() {

    }

    public function actionIn(){
        if($this->getUser()->isLoggedIn()){
            if($this->backlink){
                $this->restoreRequest($this->backlink);
                $this->backlink = null;
                return;
            }
            $this->redirect('Homepage:',['backlink'=>null]);
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
