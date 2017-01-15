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
    use Identifier, Guid, Name, Email, Phone, Address;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->created = new \DateTime();
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
     * @ORM\ManyToMany(targetEntity="OptionEntity"))
     * @var OptionEntity[]
     */
    private $options;

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
     */
    public function addInversedChild($child) {
        $this->children->add($child);
    }

    /**
     * @param ChildEntity $child
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
     * @return OptionEntity[]
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param OptionEntity $option
     */
    public function addOption($option) {
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption($option) {
        $this->options->removeElement($option);
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

}