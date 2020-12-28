<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Doctrine\Common\Collections\Criteria;
use Ticketer\Model\Database\Attributes\TCapacityAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TInternalInfoAttribute;
use Ticketer\Model\Database\Attributes\TNameAttribute;
use Ticketer\Model\Database\Attributes\TOccupancyIconAttribute;
use Ticketer\Model\Database\Attributes\TStartDateAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
use Ticketer\Model\Database\Enums\EventStateEnum;

/**
 * Událost, ke které se přihlášky vydávají
 * @package App\Model\Entities
 * @ORM\Table(name="event")
 * @ORM\Entity
 */
class EventEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TNameAttribute;
    use TStartDateAttribute;
    use TInternalInfoAttribute;
    use TOccupancyIconAttribute;
    use TCapacityAttribute {
        TCapacityAttribute::setCapacity as private setCapacityOrig;
        TCapacityAttribute::updateCapacityFull as private updateCapacityFullOrig;
    }

    /**
     * @ORM\Column(type="event_state_enum")
     * @var EventStateEnum
     */
    private EventStateEnum $state;

    /**
     * @ORM\OneToMany(targetEntity="EarlyWaveEntity", mappedBy="event", cascade={"persist","remove"})
     * @var ArrayCollection<int,EarlyWaveEntity>
     */
    private $earlyWaves;

    /**
     * @ORM\OneToMany(targetEntity="CartEntity", mappedBy="event", cascade={"persist","remove"})
     * @var ArrayCollection<int,CartEntity>
     */
    private $carts;

    /**
     * @ORM\OneToMany(targetEntity="ApplicationEntity", mappedBy="event", cascade={"persist","remove"})
     * @var ArrayCollection<int,ApplicationEntity>
     */
    private $applications;

    /**
     * @ORM\OneToMany(targetEntity="SubstituteEntity", mappedBy="event", cascade={"persist","remove"})
     * @var ArrayCollection<int,SubstituteEntity>
     */
    private $substitutes;

    /**
     * @ORM\OneToMany(targetEntity="AdditionEntity", mappedBy="event", cascade={"persist","remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @var ArrayCollection<int,AdditionEntity>
     */
    private $additions;

    /**
     * @ORM\OneToMany(targetEntity="ReservationEntity", mappedBy="event", cascade={"persist","remove"})
     * @var ArrayCollection<int,ReservationEntity>
     */
    private $reservations;

    public function __construct()
    {
        parent::__construct();
        $this->earlyWaves = new ArrayCollection();
        $this->carts = new ArrayCollection();
        $this->additions = new ArrayCollection();
        $this->substitutes = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->state = EventStateEnum::INACTIVE();
    }

    /**
     * @return EventStateEnum
     */
    public function getState(): EventStateEnum
    {
        return $this->state;
    }

    /**
     * @param EventStateEnum $state
     */
    public function setState(EventStateEnum $state): void
    {
        $this->state = $state;
    }

    /**
     * @return EarlyWaveEntity[]
     */
    public function getEarlyWaves(): array
    {
        return $this->earlyWaves->toArray();
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function addEarlyWave(EarlyWaveEntity $earlyWave): void
    {
        $earlyWave->setEvent($this);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @internal
     */
    public function addInversedEarlyWave(EarlyWaveEntity $earlyWave): void
    {
        $this->earlyWaves->add($earlyWave);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function removeEarlyWave(EarlyWaveEntity $earlyWave): void
    {
        $earlyWave->setEvent(null);
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     * @internal
     */
    public function removeInversedEarlyWave(EarlyWaveEntity $earlyWave): void
    {
        $this->earlyWaves->removeElement($earlyWave);
    }

    /**
     * @return CartEntity[]
     */
    public function getCarts(): array
    {
        return $this->carts->toArray();
    }

    /**
     * @param CartEntity $cart
     */
    public function addCart(CartEntity $cart): void
    {
        $cart->setEvent($this);
    }

    /**
     * @param CartEntity $cart
     * @internal
     */
    public function addInversedCart(CartEntity $cart): void
    {
        $this->carts->add($cart);
    }

    /**
     * @param CartEntity $cart
     */
    public function removeCart(CartEntity $cart): void
    {
        $cart->setEvent(null);
    }

    /**
     * @param CartEntity $cart
     * @internal
     */
    public function removeInversedCart(CartEntity $cart): void
    {
        $this->carts->removeElement($cart);
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getApplications(): array
    {
        return $this->applications->toArray();
    }

    /**
     * @param ApplicationEntity $application
     */
    public function addApplication(ApplicationEntity $application): void
    {
        $application->setEvent($this);
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getReservedApplications(): array
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->in('state', ApplicationStateEnum::listReserved())
            );

        return $this->applications->matching($criteria)->toArray();
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getOccupiedApplications(): array
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->in('state', ApplicationStateEnum::listOccupied())
            );

        return $this->applications->matching($criteria)->toArray();
    }

    public function setCapacity(?int $capacity): self
    {
        $changed = $capacity !== $this->getCapacity();
        $this->setCapacityOrig($capacity);
        if ($changed) {
            $this->updateCapacityFullOrig();
        }

        return $this;
    }


    /**
     * @return ApplicationEntity[]
     */
    public function getFullfilledApplications(): array
    {
        $criteria = Criteria::create()->where(
            Criteria::expr()->eq('state', ApplicationStateEnum::FULFILLED())
        );

        return $this->applications->matching($criteria)->toArray();
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getIssuedApplications(): array
    {
        $criteria = Criteria::create()->where(
            Criteria::expr()->notIn('state', ApplicationStateEnum::listNotIssued())
        );

        return $this->applications->matching($criteria)->toArray();
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function addInversedApplication(ApplicationEntity $application): void
    {
        $this->applications->add($application);
        $this->updateCapacityFull();
    }

    /**
     * @param ApplicationEntity $application
     */
    public function removeApplication(ApplicationEntity $application): void
    {
        $application->setEvent(null);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function removeInversedApplication(ApplicationEntity $application): void
    {
        $this->applications->removeElement($application);
        $this->updateCapacityFull();
    }


    /**
     * @return SubstituteEntity[]
     */
    public function getSubstitute(): array
    {
        return $this->substitutes->toArray();
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function addSubstitute(SubstituteEntity $substitute): void
    {
        $substitute->setEvent($this);
    }

    /**
     * @param SubstituteEntity $substitute
     * @internal
     */
    public function addIversedSubstitute(SubstituteEntity $substitute): void
    {
        $this->substitutes->add($substitute);
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function removeSubstitute(SubstituteEntity $substitute): void
    {
        $substitute->setEvent(null);
    }

    /**
     * @param SubstituteEntity $substitute
     * @internal
     */
    public function removeInversedSubstitute(SubstituteEntity $substitute): void
    {
        $this->substitutes->removeElement($substitute);
    }

    /**
     * @return AdditionEntity[]
     */
    public function getAdditions(): array
    {
        return $this->additions->toArray();
    }

    /**
     * @param AdditionEntity $addition
     * @internal
     */
    public function addInversedAddition(AdditionEntity $addition): void
    {
        $this->additions->add($addition);
    }

    /**
     * @param AdditionEntity $addition
     * @internal
     */
    public function removeInversedAddition(AdditionEntity $addition): void
    {
        $this->additions->removeElement($addition);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getState()->equals(EventStateEnum::ACTIVE());
    }

    /**
     * @param \DateTime|null $date
     * @return bool
     */
    public function isPublicAvailible(?\DateTime $date = null): bool
    {
        return $this->isActive() && $this->isStarted($date);
    }

    /**
     * @return ReservationEntity[]
     */
    public function getReservations(): array
    {
        return $this->reservations->toArray();
    }

    /**
     * @param ReservationEntity $reservation
     */
    public function addReservation(ReservationEntity $reservation): void
    {
        $reservation->setEvent($this);
    }

    /**
     * @param ReservationEntity $reservation
     * @internal
     */
    public function addInversedReservation(ReservationEntity $reservation): void
    {
        $this->reservations->add($reservation);
    }

    /**
     * @param ReservationEntity $reservation
     */
    public function removeReservation(ReservationEntity $reservation): void
    {
        $reservation->setEvent(null);
    }

    /**
     * @param ReservationEntity $reservation
     * @internal
     */
    public function removeInversedReservation(ReservationEntity $reservation): void
    {
        $this->reservations->removeElement($reservation);
    }

    public function countCapacityUsage(): int
    {
        return count($this->getIssuedApplications());
    }


    public function updateCapacityFull(): void
    {
        if ($this->isCapacityFull()) {
            return;
        }
        $this->updateCapacityFullOrig();
    }
}
