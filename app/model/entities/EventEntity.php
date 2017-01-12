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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EventEntity extends BaseEntity {
    use Identifier, Name, Capacity;

    public function __construct() {
        $this->earlies = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->additions = new ArrayCollection();
    }


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
     * @ORM\OneToMany(targetEntity="EarlyEntity", mappedBy="event")
     * @var EarlyEntity[]
     */
    private $earlies;

    /**
     * @ORM\OneToMany(targetEntity="OrderEntity", mappedBy="event")
     * @var OrderEntity[]
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity="AdditionEntity", mappedBy="event")
     * @var AdditionEntity[]
     */
    private $additions;

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState($state) {
        $this->state = $state;
    }
}