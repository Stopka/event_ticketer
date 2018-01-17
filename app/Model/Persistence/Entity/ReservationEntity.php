<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TCreatedAttribute;
use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Náhradník
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ReservationEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TEmailAttribute, TCreatedAttribute;

    const STATE_WAITING = 0;
    const STATE_ORDERED = 1;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_WAITING;

    /**
     * @ORM\OneToMany(targetEntity="ApplicationEntity", mappedBy="reservation")
     * @var ApplicationEntity[]
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="reservations")
     * @var EventEntity
     */
    private $event;

    /**
     * CartEntity constructor
     */
    public function __construct() {
        $this->applications = new ArrayCollection();
        $this->setCreated();
    }

    /**
     * @return ApplicationEntity[]
     */
    public function getApplications(): array {
        return $this->applications->toArray();
    }

    /**
     * @param ApplicationEntity $application
     */
    public function addApplication(ApplicationEntity $application): void {
        $application->setReservation($this);
    }

    /**
     * @internal
     * @param ApplicationEntity $application
     */
    public function addInversedApplication(ApplicationEntity $application): void {
        if($event = $this->getEvent()){
            $application->setEvent($event);
        }else if($event = $application->getEvent()){
            $this->setEvent($event);
        }
        $this->applications->add($application);
    }

    /**
     * @param ApplicationEntity $application
     */
    public function removeApplication(ApplicationEntity $application): void {
        $application->setEvent(NULL);
    }

    /**
     * @internal
     * @param ApplicationEntity $application
     */
    public function removeInversedApplication(ApplicationEntity $application): void {
        $this->applications->removeElement($application);
    }

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void {
        $this->state = $state;
    }

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(?EventEntity $event) {
        if ($this->event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->removeInversedReservation($this);
        }
        $this->event = $event;
        if ($event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addInversedReservation($this);
        }
    }

}