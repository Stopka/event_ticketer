<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Ticketer\Model\Database\Entities\PriceEntity;
use Doctrine\ORM\Mapping as ORM;

trait TPriceAttribute
{

    /**
     * @ORM\ManyToOne(targetEntity="PriceEntity", cascade={"persist","remove"})
     * @var PriceEntity|null
     */
    private $price;

    /**
     * @return \Ticketer\Model\Database\Entities\PriceEntity
     */
    public function getPrice(): ?PriceEntity
    {
        return $this->price;
    }

    /**
     * @param PriceEntity $price
     * @return $this
     */
    public function setPrice(?PriceEntity $price)
    {
        $this->price = $price;

        return $this;
    }
}
