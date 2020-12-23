<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Hodnota ceny v konkrétní měně
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceAmountEntity extends BaseEntity
{
    use TIdentifierAttribute;

    /**
     * @ORM\ManyToOne(targetEntity="PriceEntity", inversedBy="priceAmounts")
     * @var PriceEntity|null
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="CurrencyEntity")
     * @var CurrencyEntity
     */
    private $currency;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $amount = 0.0;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return PriceEntity|null
     */
    public function getPrice(): ?PriceEntity
    {
        return $this->price;
    }

    /**
     * @param PriceEntity|NULL $price
     */
    public function setPrice(?PriceEntity $price): void
    {
        if (null !== $this->price) {
            $this->price->removeInversedPriceAmount($this);
        }
        $this->price = $price;
        if (null !== $price) {
            $price->addInversedPriceAmount($this);
        }
    }

    /**
     * @return CurrencyEntity
     */
    public function getCurrency(): ?CurrencyEntity
    {
        return $this->currency;
    }

    /**
     * @param CurrencyEntity $currency
     */
    public function setCurrency(CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }
}
