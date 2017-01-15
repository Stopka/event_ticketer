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
use App\Model\Entities\Attributes\StartDate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyEntity extends BaseEntity {
    use Identifier, Name, Email, Phone, Address, StartDate;

    public function __construct() {
        $this->orders = new ArrayCollection();
    }


    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="earlies")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="OrderEntity", mappedBy="early")
     * @var OrderEntity[]|ArrayCollection
     */
    private $orders;

    /**
     * @return EventEntity
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent($event) {
        if($this->event){
            $this->event->removeInversedEarly($this);
        }
        $this->event = $event;
        if($event) {
            $event->addInversedEarly($this);
        }
    }

    /**
     * @return OrderEntity[]
     */
    public function getOrders() {
        return $this->orders;
    }

    /**
     * @param OrderEntity $order
     */
    public function addOrder($order) {
        $order->setEarly($this);
    }

    /**
     * @param OrderEntity $order
     */
    public function addIversedOrder($order) {
        $this->orders->add($order);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeOrder($order) {
        $order->setEarly(NULL);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeIversedOrder($order) {
        $this->orders->removeElement($order);
    }
}