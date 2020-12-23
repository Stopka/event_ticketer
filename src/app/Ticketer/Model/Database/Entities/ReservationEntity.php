<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Náhradník
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ReservationEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TPersonNameAttribute;
    use TEmailAttribute;
    use TCreatedAttribute;

    //TODO make it enum
    public const STATE_WAITING = 0;
    public const STATE_ORDERED = 1;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_WAITING;

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
     * CartEntity constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->applications = new ArrayCollection();
        $this->setCreated();
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
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
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
        return count($this->getApplications()) > 0 && self::STATE_WAITING === $this->getState();
    }
}
