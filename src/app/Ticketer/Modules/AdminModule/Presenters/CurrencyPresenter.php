<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\CurrencyFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Forms\ICurrencyFromWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Grids\CurrenciesGridWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\ICurrenciesGridWrapperFactory;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Entities\CurrencyEntity;

class CurrencyPresenter extends BasePresenter
{

    private ICurrenciesGridWrapperFactory $currenciesGridWrapperFactory;

    private ICurrencyFromWrapperFactory $currencyFormWrapperFactory;

    private CurrencyDao $currencyDao;

    public function __construct(
        BasePresenterDependencies $dependencies,
        CurrencyDao $currencyDao,
        ICurrenciesGridWrapperFactory $currenciesGridWrapperFactory,
        ICurrencyFromWrapperFactory $currencyFromWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->currencyDao = $currencyDao;
        $this->currenciesGridWrapperFactory = $currenciesGridWrapperFactory;
        $this->currencyFormWrapperFactory = $currencyFromWrapperFactory;
    }

    public function actionDefault(): void
    {
    }

    /**
     * @throws AbortException
     */
    public function actionAdd(): void
    {
        $this->redirect("edit");
    }

    /**
     * @param string|null $id
     * @throws AbortException
     */
    public function actionEdit(?string $id = null): void
    {
        $uuid = null === $id ? null : Uuid::fromString($id);
        $currency = null === $uuid ? null : $this->currencyDao->getCurrency($uuid);
        if (null === $currency && null !== $id) {
            $this->redirect('edit');
        }
        if (null !== $currency) {
            $this->getMenu()->setLinkParam(CurrencyEntity::class, $currency);
        }
        /** @var CurrencyFormWrapper $currencyForm */
        $currencyForm = $this->getComponent('currencyForm');
        $currencyForm->setCurrencyEntity($currency);
        $this->template->currency = $currency;
    }

    /**
     * @return CurrenciesGridWrapper
     */
    public function createComponentCurrenciesGrid(): CurrenciesGridWrapper
    {
        return $this->currenciesGridWrapperFactory->create();
    }

    /**
     * @return CurrencyFormWrapper
     */
    public function createComponentCurrencyForm(): CurrencyFormWrapper
    {
        return $this->currencyFormWrapperFactory->create();
    }
}
