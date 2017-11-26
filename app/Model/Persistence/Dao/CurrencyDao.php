<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Exception\EmptyException;
use App\Model\Persistence\Entity\CurrencyEntity;

class CurrencyDao extends EntityDao {

    protected function getEntityClass(): string {
        return CurrencyEntity::class;
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
     * @param CurrencyEntity $currency
     * @throws EmptyException
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