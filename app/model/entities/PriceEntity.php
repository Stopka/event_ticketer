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
}