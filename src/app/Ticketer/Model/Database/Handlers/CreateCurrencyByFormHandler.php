<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Handlers;

use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

class CreateCurrencyByFormHandler
{
    use TDoctrineEntityManager;

    /**
     * @param CurrencyFormValue $values
     * @return CurrencyEntity
     */
    public function handle(
        CurrencyFormValue $values,
    ): CurrencyEntity {
        $em = $this->getEntityManager();
        $currencyEntity = new CurrencyEntity();
        $currencyEntity->setName($values->name);
        $currencyEntity->setCode($values->code);
        $currencyEntity->setSymbol($values->symbol);
        $em->persist($currencyEntity);
        $em->flush();

        return $currencyEntity;
    }
}
