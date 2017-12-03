<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TEndDateAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Náhradník
 * @package App\Model\Entities
 * @ORM\Entity
 */
class SubstituteEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TEmailAttribute, TEndDateAttribute;

    const STATE_WAITING = 0;
    const STATE_ACTIVE = 1;
    const STATE_ORDERED = 2;
    const STATE_OVERDUE = 4;

    /**
     * CartEntity constructor
     */
    public function __construct() {
        $this->created = new \DateTime();
    }

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $state = self::STATE_WAITING;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="substitutes")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="EarlyEntity")
     * @var EarlyEntity
     */
    private $early;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $created;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $count;

    /**
     * @ORM\OneToOne(targetEntity="CartEntity", mappedBy="substitute")
     * @var CartEntity
     */
    private $cart;

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(EventEntity $event) {
        if($this->event){
            $event->removeIversedSubstitute($this);
        }
        $this->event = $event;
        if($event) {
            $event->addIversedSubstitute($this);
        }
    }

    /**
     * @return EarlyEntity
     */
    public function getEarly(): ?EarlyEntity {
        return $this->early;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly(EarlyEntity $early) {
        $this->early = $early;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    public function isOrdered(): bool{
        return $this->getState()==self::STATE_ORDERED;
    }

    public function isActive(): bool{
        return $this->getState()==self::STATE_ACTIVE && !$this->isEnded();
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getCount(): int {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count) {
        $this->count = $count;
    }

    /**
     * @return CartEntity
     */
    public function getCart(): ?CartEntity {
        return $this->cart;
    }

    /**
     * @param CartEntity $cart
     */
    public function setCart(?CartEntity $cart) {
        $cart->setSubstitute($this);
    }

    /**
     * @param CartEntity $cart
     */
    public function setInversedCart(?CartEntity $cart) {
        $this->cart = $cart;
        if($this->cart){
            $this->setState(self::STATE_ORDERED);
        }else{
            $this->setState(self::STATE_WAITING);
        }
    }



}