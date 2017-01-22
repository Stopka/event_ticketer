<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\IdentifierAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceEntity extends BaseEntity {
    use IdentifierAttribute;

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
    public function addPriceAmount(PriceAmountEntity $priceAmount) {
        $priceAmount->setPrice($this);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function removePriceAmount(PriceAmountEntity $priceAmount) {
        $priceAmount->setPrice(NULL);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function addInversedPriceAmount(PriceAmountEntity $priceAmount) {
        $this->priceAmounts->add($priceAmount);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function removeInversedPriceAmount(PriceAmountEntity $priceAmount) {
        $this->priceAmounts->removeElement($priceAmount);
    }

    /**
     * @param CurrencyEntity $currency
     * @return PriceAmountEntity|null
     */
    public function getPriceAmountByCurrency(CurrencyEntity $currency){
        if(!$currency){
            return NULL;
        }
        foreach ($this->getPriceAmounts() as $priceAmount){
            if($priceAmount->getCurrency()->getId()==$currency->getId()){
                return $priceAmount;
            }
        }
        return NULL;
    }

}