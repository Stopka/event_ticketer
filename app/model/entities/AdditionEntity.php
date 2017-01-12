<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\Address;
use App\Model\Entities\Attributes\Capacity;
use App\Model\Entities\Attributes\Email;
use App\Model\Entities\Attributes\Name;
use App\Model\Entities\Attributes\Phone;
use App\Model\Entities\Attributes\StartDate;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdditionEntity extends BaseEntity {
    use Identifier, Name, Capacity;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state;

    /**
     * @ORM\OneToOne(targetEntity="PriceEntity")
     * @var PriceEntity
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="additions")
     * @var EventEntity
     */
    private $event;

}