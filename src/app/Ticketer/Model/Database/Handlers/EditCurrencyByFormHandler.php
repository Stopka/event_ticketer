<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Handlers;

use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

class EditCurrencyByFormHandler
{
    use TDoctrineEntityManager;

    /**
     * @param CurrencyFormValue $values
     * @param CurrencyEntity $currencyEntity
     * @return CurrencyEntity
     */
    public function handle(
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
}
