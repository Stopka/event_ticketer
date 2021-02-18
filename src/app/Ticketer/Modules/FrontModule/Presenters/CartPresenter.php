<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\FrontModule\Controls\Grids\CartApplicationsGridWrapper;
use Ticketer\Modules\FrontModule\Controls\Grids\ICartApplicationsGridWrapperFactory;
use Ticketer\Model\DateFormatter;
use Ticketer\Model\Database\Daos\CartDao;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Modules\FrontModule\Templates\CartTemplate;

/**
 * @method CartTemplate getTemplate()
 */
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
     * @throws BadRequestException
     */
    public function actionDefault(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $cart = $this->cartDao->getViewableCartByUid($uuid);
        if (null === $cart) {
            $this->flashTranslatedMessage('Error.Cart.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(CartEntity::class, $cart);
        /** @var CartApplicationsGridWrapper $applicationsGrid */
        $applicationsGrid = $this->getComponent('applicationsGrid');
        $applicationsGrid->setCart($cart);
        $template = $this->getTemplate();
        $template->cart = $cart;
    }

    /**
     * @return CartApplicationsGridWrapper
     */
    protected function createComponentApplicationsGrid(): CartApplicationsGridWrapper
    {
        return $this->cartApplicationsGridWrapperFactory->create();
    }
}
