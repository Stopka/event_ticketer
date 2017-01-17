<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceEntity extends BaseEntity {
    use Identifier;

    public function __construct() {
        $this->priceAmounts = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity="PriceAmountEntity",mappedBy="price")
     * @var PriceAmountEntity[]
     */
    private $priceAmounts;

    /**
     * @return PriceAmountEntity[]
     */
    public function getPriceAmounts() {
        return $this->priceAmounts;
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function addPriceAmount($priceAmount) {
        $priceAmount->setPrice($this);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function removePriceAmount($priceAmount) {
        $priceAmount->setPrice(NULL);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function addInversedPriceAmount($priceAmount) {
        $this->priceAmounts->add($priceAmount);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function removeInversedPriceAmount($priceAmount) {
        $this->priceAmounts->removeElement($priceAmount);
    }

}