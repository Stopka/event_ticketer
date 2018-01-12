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
use App\Model\Persistence\Attribute\TNumberAttribute;
use App\Model\Persistence\Attribute\TOccupancyIconAttribute;
use App\Model\Persistence\Attribute\TStartDateAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Událost, ke které se přihlášky vydávají
 * @package App\Model\Entities
 * @ORM\Table(name="event",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="eventNumber_unique",
 *            columns={"number"})
 *    }
 * )
 * @ORM\Entity
 */
class EventEntity extends BaseEntity {
    use TIdentifierAttribute, TNumberAttribute, TNameAttribute, TCapacityAttribute, TStartDateAttribute, TInternalInfoAttribute, TOccupancyIconAttribute;

    const STATE_INACTIVE = 0;
    const STATE_ACTIVE = 1;
    const STATE_CLOSED = 2;
    const STATE_CANCELLED = 3;

    public function __construct() {
        $this->earlyWaves = new ArrayCollection();
        $this->carts = new ArrayCollection();
        $this->additions = new ArrayCollection();
        $this->substitutes = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_INACTIVE;

    /**
     * @ORM\OneToMany(targetEntity="EarlyWaveEntity", mappedBy="event")
     * @var EarlyWaveEntity[]
     */
    private $earlyWaves;

    /**
     * @ORM\OneToMany(targetEntity="CartEntity", mappedBy="event")
     * @var CartEntity[]
     */
    private $carts;

    /**
     * @ORM\OneToMany(targetEntity="SubstituteEntity", mappedBy="event")
     * @var SubstituteEntity[]
     */
    private $substitutes;

    /**
     * @ORM\OneToMany(targetEntity="AdditionEntity", mappedBy="event")
     * @ORM\OrderBy({"position" = "ASC"})
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
    public function getEarlyWaves(): array {
        return $this->earlyWaves->toArray();
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
     * @return CartEntity[]
     */
    public function getCarts(): array {
        return $this->carts->toArray();
    }

    /**
     * @param CartEntity $cart
     */
    public function addCart(CartEntity $cart): void {
        $cart->setEvent($this);
    }

    /**
     * @param CartEntity $cart
     */
    public function addIversedCart(CartEntity $cart): void {
        $this->carts->add($cart);
    }

    /**
     * @param CartEntity $cart
     */
    public function removeCart(CartEntity $cart): void {
        $cart->setEvent(NULL);
    }

    /**
     * @param CartEntity $cart
     */
    public function removeIversedCart(CartEntity $cart): void {
        $this->carts->removeElement($cart);
    }


    /**
     * @return SubstituteEntity[]
     */
    public function getSubstitute(): array {
        return $this->substitutes->toArray();
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
    public function getAdditions(): array {
        return $this->additions->toArray();
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