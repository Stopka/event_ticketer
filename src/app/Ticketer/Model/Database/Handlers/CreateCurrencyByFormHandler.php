<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

class CreateCurrencyByFormHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param CurrencyFormValue $values
     * @return CurrencyEntity
     */
    public function handle(
        CurrencyFormValue $values,
    ): CurrencyEntity {
        $em = $this->entityManager;
        $currencyEntity = new CurrencyEntity();
        $currencyEntity->setName($values->name);
        $currencyEntity->setCode($values->code);
        $currencyEntity->setSymbol($values->symbol);
        $em->persist($currencyEntity);
        $em->flush();

        return $currencyEntity;
    }
}
