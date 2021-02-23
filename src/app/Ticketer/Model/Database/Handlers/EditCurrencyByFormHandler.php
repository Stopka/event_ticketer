<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

class EditCurrencyByFormHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CurrencyFormValue $values
     * @param CurrencyEntity $currencyEntity
     * @return CurrencyEntity
     */
    public function handle(
        CurrencyFormValue $values,
        CurrencyEntity $currencyEntity
    ): CurrencyEntity {
        $em = $this->entityManager;
        $currencyEntity->setName($values->name);
        $currencyEntity->setCode($values->code);
        $currencyEntity->setSymbol($values->symbol);
        $em->flush();

        return $currencyEntity;
    }
}
