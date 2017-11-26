<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TCapacityAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TInternalInfoAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use App\Model\Persistence\Attribute\TStartDateAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Událost, ke které se přihlášky vydávají
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EventEntity extends BaseEntity {
    use TIdentifierAttribute, TNameAttribute, TCapacityAttribute, TStartDateAttribute, TInternalInfoAttribute;

    const STATE_INACTIVE = 0;
    const STATE_ACTIVE = 1;
    const STATE_CLOSED = 2;
    const STATE_CANCELLED = 3;

    public function __construct() {
        $this->earlyWaves = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->additions = new ArrayCollection();
        $this->substitutes = new ArrayCollection();
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
     * @ORM\OneToMany(targetEntity="SubstituteEntity", mappedBy="event")
     * @var SubstituteEntity[]
     */
    private $substitutes;

    /**
     * @ORM\OneToMany(targetEntity="AdditionEntity", mappedBy="event")
     * @var AdditionEntity[]
     */
    private $additions;

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state) {
        $this->state = $state;
    }

    /**
     * @return EarlyWaveEntity[]
     */
    public function getEarlyWaves(): ArrayCollection {
        return $this->earlyWaves;
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function addEarlyWave(EarlyWaveEntity $earlyWave): void {
        $earlyWave->setEvent($this);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @internal
     */
    public function addInversedEarlyWave(EarlyWaveEntity $earlyWave): void {
        $this->earlyWaves->add($earlyWave);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function removeEarlyWave(EarlyWaveEntity $earlyWave): void {
        $earlyWave->setEvent(NULL);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @internal
     */
    public function removeInversedEarlyWave(EarlyWaveEntity $earlyWave): void {
        $this->earlyWaves->removeElement($earlyWave);
    }

    /**
     * @return OrderEntity[]
     */
    public function getOrders(): ArrayCollection {
        return $this->orders;
    }

    /**
     * @param OrderEntity $order
     */
    public function addOrder(OrderEntity $order): void {
        $order->setEvent($this);
    }

    /**
     * @param OrderEntity $order
     */
    public function addIversedOrder(OrderEntity $order): void {
        $this->orders->add($order);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeOrder(OrderEntity $order): void {
        $order->setEvent(NULL);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeIversedOrder(OrderEntity $order): void {
        $this->orders->removeElement($order);
    }


    /**
     * @return SubstituteEntity[]
     */
    public function getSubstitute(): ArrayCollection {
        return $this->substitutes;
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function addSubstitute(SubstituteEntity $substitute): void {
        $substitute->setEvent($this);
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function addIversedSubstitute(SubstituteEntity $substitute): void {
        $this->substitutes->add($substitute);
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function removeSubstitute(SubstituteEntity $substitute): void {
        $substitute->setEvent(NULL);
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function removeIversedSubstitute(SubstituteEntity $substitute): void {
        $this->substitutes->removeElement($substitute);
    }

    /**
     * @return AdditionEntity[]
     */
    public function getAdditions(): ArrayCollection {
        return $this->additions;
    }

    /**
     * @param AdditionEntity $additions
     * @internal
     */
    public function addInversedAddition(AdditionEntity $addition): void {
        $this->additions->add($addition);
    }

    /**
     * @param AdditionEntity $additions
     * @internal
     */
    public function removeInversedAddition(AdditionEntity $addition): void {
        $this->additions->removeElement($addition);
    }

    /**
     * @return bool
     */
    public function isActive(): bool {
        return $this->getState() == self::STATE_ACTIVE;
    }

    /**
     * @param \DateTime|null $date
     * @return bool
     */
    public function isPublicAvailible(?\DateTime $date = null): bool {
        return $this->isActive() && $this->isStarted($date);
    }

}