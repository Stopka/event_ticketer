<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Exception\EmptyException;
use App\Model\Exception\ORMException;
use App\Model\Persistence\Entity\CurrencyEntity;
use Grido\DataSources\Doctrine;
use Grido\DataSources\IDataSource;

class CurrencyDao extends EntityDao {

    protected function getEntityClass(): string {
        return CurrencyEntity::class;
    }

    /**
     * @param null|int $currencyId
     * @return CurrencyEntity|null
     */
    public function getCurrency(?int $currencyId): ?CurrencyEntity {
        /** @var CurrencyEntity $result */
        $result = $this->get($currencyId);
        return $result;
    }

    /**
     * @param null|string $currencyCode
     * @return CurrencyEntity|null
     */
    public function getCurrencyByCode(?string $currencyCode): ?CurrencyEntity{
        /** @var CurrencyEntity $result */
        $result = $this->getRepository()->findOneBy(['code'=>$currencyCode]);
        return $result;
    }

    /**
     * @return CurrencyEntity[]
     */
    public function getAllCurrecies(): array{
        return $this->getRepository()->findAll();
    }

    /**
     * @return IDataSource
     */
    public function getAllCurrenciesGridModel(): IDataSource{
        $qb = $this->getRepository()->createQueryBuilder('a');
        return new Doctrine($qb);
    }

    /**
     * @return CurrencyEntity
     * @throws EmptyException
     */
    public function getDefaultCurrency(): CurrencyEntity{
        $currency = $this->getRepository()->findOneBy(['default'=>true]);
        if($currency){
            return $currency;
        }
        return $this->setDefaultCurrency();
    }

    /**
     * @param CurrencyEntity|null $currency
     * @return CurrencyEntity
     * @throws ORMException
     */
    public function setDefaultCurrency(?CurrencyEntity $currency = null):CurrencyEntity{
        /** @var CurrencyEntity[] $currencies */
        $currencies = $this->getRepository()->findAll();
        if(!count($currencies)){
            throw new EmptyException("There are no currencies!");
        }
        if(!$currency && $currencies){
            $currency = $currencies[0];
        }
        foreach ($currencies as $c){
            $c->setDefault($c->getId()==$currency->getId());
        }
        $this->getEntityManager()->flush();
        return $currency->setDefault(true);
    }

}