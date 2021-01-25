<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Controls\Forms\ICartFormWrapperFactory;
use Ticketer\Model\Database\Daos\SubstituteDao;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Dtos\Uuid;

class SubstitutePresenter extends BasePresenter
{
    public ICartFormWrapperFactory $cartFormWrapperFactory;

    public SubstituteDao $substituteDao;

    /**
     * SubstitutePresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param SubstituteDao $substituteDao
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        ICartFormWrapperFactory $cartFormWrapperFactory,
        SubstituteDao $substituteDao
    ) {
        parent::__construct($dependencies);
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteDao = $substituteDao;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionDefault(string $id): void
    {
        $this->redirect('register', $id);
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionRegister(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $substitute = $this->substituteDao->getReadySubstitute($uuid);
        if (null === $substitute) {
            $this->flashTranslatedMessage('Error.Substitute.NotFound', FlashMessageTypeEnum::WARNING());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(SubstituteEntity::class, $substitute);
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setSubstitute($substitute);
        $event = $substitute->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm(): CartFormWrapper
    {
        return $this->cartFormWrapperFactory->create();
    }
}
