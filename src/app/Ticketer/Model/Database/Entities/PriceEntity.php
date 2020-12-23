<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Seznam cen v různých měnách
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceEntity extends BaseEntity
{
    use TIdentifierAttribute;

    /**
     * @ORM\OneToMany(targetEntity="PriceAmountEntity",mappedBy="price", cascade={"persist","remove"})
     * @var ArrayCollection<int,PriceAmountEntity>
     */
    private $priceAmounts;

    public function __construct()
    {
        parent::__construct();
        $this->priceAmounts = new ArrayCollection();
    }

    /**
     * @return PriceAmountEntity[]
     */
    public function getPriceAmounts(): array
    {
        return $this->priceAmounts->toArray();
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function addPriceAmount(PriceAmountEntity $priceAmount): void
    {
        $priceAmount->setPrice($this);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function removePriceAmount(PriceAmountEntity $priceAmount): void
    {
        $priceAmount->setPrice(null);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function addInversedPriceAmount(PriceAmountEntity $priceAmount): void
    {
        $this->priceAmounts->add($priceAmount);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function removeInversedPriceAmount(PriceAmountEntity $priceAmount): void
    {
        $this->priceAmounts->removeElement($priceAmount);
    }

    /**
     * @param CurrencyEntity|null $currency
     * @return PriceAmountEntity|null
     */
    public function getPriceAmountByCurrency(?CurrencyEntity $currency): ?PriceAmountEntity
    {
        if (null === $currency) {
            return null;
        }
        foreach ($this->getPriceAmounts() as $priceAmount) {
            $priceCurrency = $priceAmount->getCurrency();
            if (null !== $priceCurrency && $priceCurrency->getId() === $currency->getId()) {
                return $priceAmount;
            }
        }

        return null;
    }
}
