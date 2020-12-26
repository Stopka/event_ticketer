<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Grids\CartApplicationsGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\ICartApplicationsGridWrapperFactory;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Controls\Forms\ICartFormWrapperFactory;
use Ticketer\Model\DateFormatter;
use Ticketer\Model\Database\Daos\CartDao;
use Ticketer\Model\Database\Entities\CartEntity;

class CartPresenter extends BasePresenter
{

    private CartDao $cartDao;

    public ICartFormWrapperFactory $cartFormFactory;

    public ICartApplicationsGridWrapperFactory $cartApplicationsGridFactory;

    public DateFormatter $dateFormatter;

    /**
     * CartPresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param DateFormatter $dateFormatter
     * @param CartDao $cartDao
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        DateFormatter $dateFormatter,
        CartDao $cartDao,
        ICartFormWrapperFactory $cartFormWrapperFactory,
        ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->cartDao = $cartDao;
        $this->cartFormFactory = $cartFormWrapperFactory;
        $this->cartApplicationsGridFactory = $cartApplicationsGridWrapperFactory;
        $this->dateFormatter = $dateFormatter;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionDefault(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $cart = $this->cartDao->getCart($uuid);
        if (null === $cart) {
            $this->flashTranslatedMessage('Error.Cart.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(CartEntity::class, $cart);
        /** @var CartApplicationsGridWrapper $cartApplicationsGrid */
        $cartApplicationsGrid = $this->getComponent('cartApplicationsGrid');
        $cartApplicationsGrid->setCart($cart);
        $this->template->dateFormatter = $this->dateFormatter;
        $this->template->cart = $cart;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionEdit(string $id): void
    {
        $uuid = Uuid::fromString($id);
        $cart = $this->cartDao->getCart($uuid);
        if (null === $cart) {
            $this->flashTranslatedMessage('Error.Cart.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(CartEntity::class, $cart);
        /** @var CartFormWrapper $cartForm */
        $cartForm = $this->getComponent('cartForm');
        $cartForm->setCart($cart);
        $this->template->cart = $cart;
    }

    protected function createComponentCartForm(): CartFormWrapper
    {
        return $this->cartFormFactory->create(true);
    }

    protected function createComponentCartApplicationsGrid(): CartApplicationsGridWrapper
    {
        return $this->cartApplicationsGridFactory->create();
    }
}
