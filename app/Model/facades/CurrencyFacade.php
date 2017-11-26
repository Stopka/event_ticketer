<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\Persistence\Entity\CurrencyEntity;

class CurrencyFacade extends EntityFacade {

    protected function getEntityClass() {
        return CurrencyEntity::class;
    }

    /**
     * @return CurrencyEntity
     */
    public function getDefaultCurrency(){
        $currency = $this->getRepository()->findOneBy(['default'=>true]);
        if($currency){
            return $currency;
        }
        return $this->setDefaultCurrency();
    }

    /**
     * @param CurrencyEntity $currency
     */
    public function setDefaultCurrency(CurrencyEntity $currency = null){
        /** @var CurrencyEntity[] $currencies */
        $currencies = $this->getRepository()->findAll();
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