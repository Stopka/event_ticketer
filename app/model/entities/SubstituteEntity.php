<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\EmailAttribute;
use App\Model\Entities\Attributes\EndDateAttribute;
use App\Model\Entities\Attributes\GuidAttribute;
use App\Model\Entities\Attributes\IdentifierAttribute;
use App\Model\Entities\Attributes\PersonNameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class SubstituteEntity extends BaseEntity {
    use IdentifierAttribute, GuidAttribute, PersonNameAttribute, EmailAttribute, EndDateAttribute;

    const STATE_WAITING = 0;
    const STATE_ACTIVE = 1;
    const STATE_ORDERED = 2;
    const STATE_OVERDUE = 4;

    /**
     * OrderEntity constructor.
     * @param bool $substitute
     */
    public function __construct() {
        $this->created = new \DateTime();
        $this->generateGuid();
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
     * @ORM\OneToOne(targetEntity="OrderEntity", mappedBy="substitute")
     * @var OrderEntity
     */
    private $order;

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

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    public function isOrdered(){
        return $this->getState()==self::STATE_ORDERED;
    }

    public function isActive(){
        return $this->getState()==self::STATE_ACTIVE && !$this->isEnded();
    }

    /**
     * @param int $state
     */
    public function setState($state) {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count) {
        $this->count = $count;
    }

    /**
     * @return OrderEntity
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @param OrderEntity $order
     */
    public function setOrder($order) {
        $order->setSubstitute($this);
    }

    /**
     * @param OrderEntity $order
     */
    public function setInversedOrder($order) {
        $this->order = $order;
        if($this->order){
            $this->setState(self::STATE_ORDERED);
        }else{
            $this->setState(self::STATE_WAITING);
        }
    }



}