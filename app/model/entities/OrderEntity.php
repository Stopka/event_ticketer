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
use App\Model\Entities\Attributes\Guid;
use App\Model\Entities\Attributes\Identifier;
use App\Model\Entities\Attributes\PersonName;
use App\Model\Entities\Attributes\Phone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OrderEntity extends BaseEntity {
    use Identifier, Guid, PersonName, Email, Phone, Address;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->created = new \DateTime();
        $this->generateGuid();
    }

    /**
     * @ORM\OneToMany(targetEntity="ChildEntity", mappedBy="parent"))
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
     * @var \DateTime
     */
    private $created;

    /**
     * @return ChildEntity[]
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * @param ChildEntity $child
     */
    public function addChild($child) {
        $child->setParent($this);
    }

    /**
     * @param ChildEntity $child
     */
    public function removeChild($child) {
        $child->setParent(NULL);
    }

    /**
     * @param ChildEntity $child
     * @internal
     */
    public function addInversedChild($child) {
        $this->children->add($child);
    }

    /**
     * @param ChildEntity $child
     * @internal
     */
    public function removeInversedChild($child) {
        $this->children->removeElement($child);
    }

    /**
     * @return EventEntity
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent($event) {
        if($this->event){
            $event->removeIversedOrder($this);
        }
        $this->event = $event;
        if($event) {
            $event->addIversedOrder($this);
        }
    }

    /**
     * @return EarlyEntity
     */
    public function getEarly() {
        return $this->early;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly($early) {
        $this->early = $early;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

}