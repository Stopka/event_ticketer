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
class OrderEntity extends BaseEntity {
    use Identifier, Name, Email, Phone, Address;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->addititions = new ArrayCollection();
    }


    /**
     * @var string
     */
    private $nid;

    /**
     * @var ChildEntity[]
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="orders")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="EarlyEntity", inversedBy="orders")
     * @var EarlyEntity
     */
    private $early;

    /**
     * @ORM\ManyToMany(targetEntity="AdditionEntity"))
     * @var AdditionEntity[]
     */
    private $addititions;

    /**
     * @var \DateTime
     */
    private $created;

}