<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use App\Model\Persistence\Entity\PriceEntity;
use Doctrine\ORM\Mapping as ORM;

trait TPriceAttribute {

    /**
     * @ORM\ManyToOne(targetEntity="PriceEntity", cascade={"persist","remove"})
     * @var PriceEntity
     */
    private $price;

    /**
     * @return \App\Model\Persistence\Entity\PriceEntity
     */
    public function getPrice(): ?PriceEntity {
        return $this->price;
    }

    /**
     * @param PriceEntity $price
     * @return $this
     */
    public function setPrice(?PriceEntity $price) {
        $this->price = $price;
        return $this;
    }

}