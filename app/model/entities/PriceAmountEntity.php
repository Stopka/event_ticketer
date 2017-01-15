<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceAmountEntity extends BaseEntity {
    use Identifier;

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
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount) {
        $this->amount = $amount;
    }

    /**
     * @return PriceEntity
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param PriceEntity|NULL $price
     */
    public function setPrice($price) {
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
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param CurrencyEntity $currency
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
    }


}