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
    use Identifier, Name, Capacity, StartDate;

    const STATE_INACTIVE=false;
    const STATE_ACTIVE=true;

    public function __construct() {
        $this->earlyWaves = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->additions = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity="EarlyWaveEntity", mappedBy="event")
     * @var EarlyWaveEntity[]
     */
    private $earlyWaves;

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
     * @return EarlyWaveEntity[]
     */
    public function getEarlyWaves() {
        return $this->earlyWaves;
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function addEarlyWave($earlyWave) {
        $earlyWave->setEvent($this);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @internal
     */
    public function addInversedEarlyWave($earlyWave) {
        $this->earlyWaves->add($earlyWave);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function removeEarlyWave($earlyWave) {
        $earlyWave->setEvent(NULL);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @internal
     */
    public function removeInversedEarlyWave($earlyWave) {
        $this->earlyWaves->removeElement($earlyWave);
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
     * @internal
     */
    public function addInversedAddition($addition) {
        $this->additions->add($addition);
    }

    /**
     * @param AdditionEntity $additions
     * @internal
     */
    public function removeInversedAddition($addition) {
        $this->additions->removeElement($addition);
    }

    /**
     * @return bool
     */
    public function isActive(){
        return $this->getState()==self::STATE_ACTIVE;
    }

    /**
     * @param \DateTime|null $date
     * @return bool
     */
    public function isPublicAvailible(\DateTime $date = null){
        return $this->isActive()&&$this->isStarted($date);
    }

}