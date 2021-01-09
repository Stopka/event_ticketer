<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Database\Enums\ReservationStateEnum;

/**
 * Náhradník
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ReservationEntity extends BaseEntity implements NumberableInterface
{
    use TIdentifierAttribute;
    use TPersonNameAttribute;
    use TEmailAttribute;
    use TCreatedAttribute;

    /**
     * @ORM\Column(type="reservation_state_enum")
     * @var ReservationStateEnum
     */
    private ReservationStateEnum $state;

    /**
     * @ORM\OneToMany(targetEntity="ApplicationEntity", mappedBy="reservation")
     * @var ArrayCollection<int,ApplicationEntity>
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="reservations")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\OneToOne(targetEntity="ReservationNumberEntity", cascade={"persist","remove"})
     * @var ReservationNumberEntity
     */
    private ReservationNumberEntity $number;

    /**
     * CartEntity constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->applications = new ArrayCollection();
        $this->setCreated();
        $this->number = new ReservationNumberEntity();
        $this->state = ReservationStateEnum::WAITING();
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number->getId();
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
        $application->setReservation($this);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function addInversedApplication(ApplicationEntity $application): void
    {
        $this->applications->add($application);
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
    }

    /**
     * @return ReservationStateEnum
     */
    public function getState(): ReservationStateEnum
    {
        return $this->state;
    }

    /**
     * @param ReservationStateEnum $state
     */
    public function setState(ReservationStateEnum $state): void
    {
        $this->state = $state;
    }

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity
    {
        return $this->event;
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(?EventEntity $event): void
    {
        if (null !== $this->event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->event->removeInversedReservation($this);
        }
        $this->event = $event;
        if (null !== $event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addInversedReservation($this);
        }
    }

    public function isRegisterReady(): bool
    {
        return count($this->getApplications()) > 0 && $this->getState()->equals(ReservationStateEnum::WAITING());
    }
}
