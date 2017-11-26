<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TIdentifierAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Hodnota ceny v konkrétní měně
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceAmountEntity extends BaseEntity {
    use TIdentifierAttribute;

    /**
     * @ORM\ManyToOne(targetEntity="PriceEntity", inversedBy="priceAmounts")
     * @var PriceEntity
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
    private $amount;

    /**
     * @return float
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount) {
        $this->amount = $amount;
    }

    /**
     * @return PriceEntity
     */
    public function getPrice(): PriceEntity {
        return $this->price;
    }

    /**
     * @param PriceEntity|NULL $price
     */
    public function setPrice(?PriceEntity $price): void {
        if($this->price){
            $this->price->removeInversedPriceAmount($this);
        }
        $this->price = $price;
        if($price) {
            $price->addInversedPriceAmount($this);
        }
    }

    /**
     * @return CurrencyEntity
     */
    public function getCurrency(): ?CurrencyEntity {
        return $this->currency;
    }

    /**
     * @param CurrencyEntity $currency
     */
    public function setCurrency(CurrencyEntity $currency): void {
        $this->currency = $currency;
    }


}