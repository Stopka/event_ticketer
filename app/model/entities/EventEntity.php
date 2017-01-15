<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\Capacity;
use App\Model\Entities\Attributes\Name;
use App\Model\Entities\Attributes\Price;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EventEntity extends BaseEntity {
    use Identifier, Name, Capacity, Price;

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

    /**
     * @return PriceEntity
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param PriceEntity $price
     */
    public function setPrice($price) {
        $this->price = $price;
    }

    /**
     * @return EarlyEntity[]
     */
    public function getEarlies() {
        return $this->earlies;
    }

    /**
     * @param EarlyEntity $early
     */
    public function addEarly($early) {
        $early->setEvent($this);
    }

    /**
     * @param EarlyEntity $early
     */
    public function addInversedEarly($early) {
        $this->earlies->add($early);
    }

    /**
     * @param EarlyEntity $early
     */
    public function removeEarly($early) {
        $early->setEvent(NULL);
    }

    /**
     * @param EarlyEntity $early
     */
    public function removeInversedEarly($early) {
        $this->earlies->removeElement($early);
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
        $order->setEvent($this);
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
        $order->setEvent(NULL);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeIversedOrder($order) {
        $this->orders->removeElement($order);
    }


    /**
     * @return AdditionEntity[]
     */
    public function getAdditions() {
        return $this->additions;
    }

    /**
     * @param AdditionEntity $additions
     */
    public function addInversedAddition($addition) {
        $this->additions->add($addition);
    }

    /**
     * @param AdditionEntity $additions
     */
    public function removeInversedAddition($addition) {
        $this->additions->removeElement($addition);
    }


}