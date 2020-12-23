<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Modules\FrontModule\Controls\Grids\CartApplicationsGridWrapper;
use Ticketer\Modules\FrontModule\Controls\Grids\ICartApplicationsGridWrapperFactory;
use Ticketer\Model\DateFormatter;
use Ticketer\Model\Database\Daos\CartDao;
use Ticketer\Model\Database\Entities\CartEntity;

class CartPresenter extends BasePresenter
{

    public CartDao $cartDao;

    public ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory;

    public DateFormatter $dateFormatter;

    public function __construct(
        BasePresenterDependencies $dependencies,
        DateFormatter $dateFormatter,
        CartDao $cartDao,
        ICartApplicationsGridWrapperFactory $cartApplicationsGridWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->cartDao = $cartDao;
        $this->cartApplicationsGridWrapperFactory = $cartApplicationsGridWrapperFactory;
        $this->dateFormatter = $dateFormatter;
    }

    /**
     * @param string $id
     * @throws AbortException
     */
    public function actionDefault(string $id): void
    {
        $cart = $this->cartDao->getViewableCartByUid($id);
        if (null === $cart) {
            $this->flashTranslatedMessage('Error.Cart.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(CartEntity::class, $cart);
        /** @var CartApplicationsGridWrapper $applicationsGrid */
        $applicationsGrid = $this->getComponent('applicationsGrid');
        $applicationsGrid->setCart($cart);
        $this->template->cart = $cart;
        $this->template->dateFormatter = $this->dateFormatter;
    }

    /**
     * @return CartApplicationsGridWrapper
     */
    protected function createComponentApplicationsGrid(): CartApplicationsGridWrapper
    {
        return $this->cartApplicationsGridWrapperFactory->create();
    }
}
