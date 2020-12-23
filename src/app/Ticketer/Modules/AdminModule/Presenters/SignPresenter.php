<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Modules\AdminModule\Controls\Forms\ISignInFormWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Forms\SignInFormWrapper;

class SignPresenter extends BasePresenter
{
    public ISignInFormWrapperFactory $signInFromWrapperFactory;

    /**
     * @var string|null
     * @persistent
     */
    public ?string $backlink = '';

    /**
     * SignPresenter constructor.
     * @param ISignInFormWrapperFactory $signInFormWrapperFactory
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        ISignInFormWrapperFactory $signInFormWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->signInFromWrapperFactory = $signInFormWrapperFactory;
    }


    protected function checkUser(): void
    {
    }

    /**
     * @throws AbortException
     */
    public function actionIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            if (null !== $this->backlink) {
                $this->restoreRequest($this->backlink);
                $this->backlink = null;

                return;
            }
            $this->redirect('Homepage:', ['backlink' => null]);
        }
    }

    /**
     * @throws AbortException
     */
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->redirect('Homepage:');
    }

    /**
     * @return SignInFormWrapper
     */
    protected function createComponentSignInForm(): SignInFormWrapper
    {
        return $this->signInFromWrapperFactory->create();
    }
}
