<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Exceptions\EmptyException;
use Ticketer\Model\Exceptions\ORMException;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;

class CurrencyDao extends EntityDao
{
    protected function getEntityClass(): string
    {
        return CurrencyEntity::class;
    }

    /**
     * @param Uuid $currencyId
     * @return CurrencyEntity|null
     */
    public function getCurrency(Uuid $currencyId): ?CurrencyEntity
    {
        /** @var CurrencyEntity $result */
        $result = $this->get($currencyId);

        return $result;
    }

    /**
     * @param null|string $currencyCode
     * @return CurrencyEntity|null
     */
    public function getCurrencyByCode(?string $currencyCode): ?CurrencyEntity
    {
        /** @var CurrencyEntity|null $result */
        $result = $this->getRepository()->findOneBy(['code' => $currencyCode]);

        return $result;
    }

    /**
     * @return CurrencyEntity[]
     */
    public function getAllCurrecies(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @return IDataSource
     */
    public function getAllCurrenciesGridModel(): IDataSource
    {
        $qb = $this->getRepository()->createQueryBuilder('a');

        return new DoctrineDataSource($qb, 'id');
    }

    /**
     * @return CurrencyEntity
     * @throws EmptyException
     */
    public function getDefaultCurrency(): CurrencyEntity
    {
        /** @var CurrencyEntity|null $currency */
        $currency = $this->getRepository()->findOneBy(['default' => true]);
        if (null !== $currency) {
            return $currency;
        }

        return $this->setDefaultCurrency();
    }

    /**
     * @param CurrencyEntity|null $currency
     * @return CurrencyEntity
     * @throws ORMException
     */
    public function setDefaultCurrency(?CurrencyEntity $currency = null): CurrencyEntity
    {
        /** @var CurrencyEntity[] $currencies */
        $currencies = $this->getRepository()->findAll();
        if (0 === count($currencies)) {
            throw new EmptyException("There are no currencies!");
        }
        if (null === $currency) {
            $currency = $currencies[0];
        }
        foreach ($currencies as $currencyItem) {
            $currencyItem->setDefault($currencyItem->getId() === $currency->getId());
        }
        $this->getEntityManager()->flush();

        return $currency->setDefault(true);
    }
}
