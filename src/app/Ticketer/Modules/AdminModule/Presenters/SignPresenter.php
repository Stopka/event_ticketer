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
     * SignPresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param ISignInFormWrapperFactory $signInFormWrapperFactory
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        ISignInFormWrapperFactory $signInFormWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->signInFromWrapperFactory = $signInFormWrapperFactory;
    }


    protected function assertLoggedUser(): void
    {
    }

    /**
     * @param string|null $backlink
     * @throws AbortException
     */
    public function actionIn(?string $backlink = null): void
    {
        if ($this->getUser()->isLoggedIn()) {
            if (null !== $backlink) {
                $this->restoreRequest($backlink);
            }
            $this->redirect('Homepage:');
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
