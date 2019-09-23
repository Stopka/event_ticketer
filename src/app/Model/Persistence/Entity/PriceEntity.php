<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TIdentifierAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Seznam cen v různých měnách
 * @package App\Model\Entities
 * @ORM\Entity
 */
class PriceEntity extends BaseEntity {
    use TIdentifierAttribute;

    public function __construct() {
        parent::__construct();
        $this->priceAmounts = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity="PriceAmountEntity",mappedBy="price", cascade={"persist","remove"})
     * @var PriceAmountEntity[]
     */
    private $priceAmounts;

    /**
     * @return PriceAmountEntity[]
     */
    public function getPriceAmounts(): array {
        return $this->priceAmounts->toArray();
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function addPriceAmount(PriceAmountEntity $priceAmount): void {
        $priceAmount->setPrice($this);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     */
    public function removePriceAmount(PriceAmountEntity $priceAmount): void {
        $priceAmount->setPrice(NULL);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function addInversedPriceAmount(PriceAmountEntity $priceAmount): void {
        $this->priceAmounts->add($priceAmount);
    }

    /**
     * @param PriceAmountEntity $priceAmount
     * @internal
     */
    public function removeInversedPriceAmount(PriceAmountEntity $priceAmount): void {
        $this->priceAmounts->removeElement($priceAmount);
    }

    /**
     * @param CurrencyEntity $currency
     * @return PriceAmountEntity|null
     */
    public function getPriceAmountByCurrency(CurrencyEntity $currency): ?PriceAmountEntity{
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