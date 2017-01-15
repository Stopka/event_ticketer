<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use App\Model\Entities\PriceEntity;
use Doctrine\ORM\Mapping as ORM;

trait Price {

    /**
     * @ORM\OneToOne(targetEntity="PriceEntity")
     * @var PriceEntity
     */
    private $price;

    /**
     * @return PriceEntity
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param PriceEntity $price
     * @return $this
     */
    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

}