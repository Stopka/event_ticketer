<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\Address;
use App\Model\Entities\Attributes\Email;
use App\Model\Entities\Attributes\Name;
use App\Model\Entities\Attributes\Phone;
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
}