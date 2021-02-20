<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

class CurrencyManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * CurrencyManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param CurrencyDao $currencyDao
     */
    public function __construct(EntityManagerWrapper $entityManager, CurrencyDao $currencyDao)
    {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param CurrencyFormValue $values
     * @param CurrencyEntity $currencyEntity
     * @return CurrencyEntity
     */
    public function editCurrencyFromCurrencyForm(
        CurrencyFormValue $values,
        CurrencyEntity $currencyEntity
    ): CurrencyEntity {
        $em = $this->getEntityManager();
        $currencyEntity->setName($values->name);
        $currencyEntity->setCode($values->code);
        $currencyEntity->setSymbol($values->symbol);
        $em->flush();

        return $currencyEntity;
    }

    /**
     * @param CurrencyFormValue $values
     * @return CurrencyEntity
     */
    public function createCurrencyFromCurrencyForm(CurrencyFormValue $values): CurrencyEntity
    {
        $em = $this->getEntityManager();
        $currencyEntity = new CurrencyEntity();
        $currencyEntity->setName($values->name);
        $currencyEntity->setCode($values->code);
        $currencyEntity->setSymbol($values->symbol);
        $em->persist($currencyEntity);

        return $this->editCurrencyFromCurrencyForm($values, $currencyEntity);
    }

    /**
     * @param CurrencyEntity|null $currencyEntity
     * @return CurrencyEntity
     */
    public function setDefaultCurrency(?CurrencyEntity $currencyEntity): CurrencyEntity
    {
        return $this->currencyDao->setDefaultCurrency($currencyEntity);
    }
}
