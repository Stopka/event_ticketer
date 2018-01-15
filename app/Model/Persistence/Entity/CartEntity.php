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
use App\Model\Persistence\Attribute\TNumberAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use App\Model\Persistence\Attribute\TPhoneAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objednávka, seskupení naráz vydaných přihlášek
 * @package App\Model\Entities
 * @ORM\Table(name="cart",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="cartNumber_unique",
 *            columns={"number","event_id"})
 *    }
 * )
 * @ORM\Entity
 */
class CartEntity extends BaseEntity {
    use TIdentifierAttribute, TNumberAttribute, TPersonNameAttribute, TEmailAttribute, TPhoneAttribute, TCreatedAttribute;

    const STATE_RESERVED = 0;
    const STATE_ORDERED = 0;

    /**
     * CartEntity constructor
     */
    public function __construct($reservation) {
        $this->applications = new ArrayCollection();
        if ($reservation) {
            $this->setState(self::STATE_RESERVED);
        }
        $this->setCreated();
    }

    protected function getLastNumberSearchCriteria(): array {
        return ["event.id" => $this->getEvent()->getId()];
    }


    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $state = self::STATE_ORDERED;

    /**
     * @ORM\OneToMany(targetEntity="ApplicationEntity", mappedBy="cart"))
     * @var ApplicationEntity[]
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="carts")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="EarlyEntity", inversedBy="carts")
     * @var EarlyEntity
     */
    private $early;

    /**
     * @ORM\OneToOne(targetEntity="SubstituteEntity", inversedBy="cart")
     * @var SubstituteEntity
     */
    private $substitute;

    /**
     * @ORM\OneToOne(targetEntity="ReservationEntity", inversedBy="cart")
     * @var ReservationEntity
     */
    private $reservation;

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
        $application->setCart($this);
    }

    /**
     * @param ApplicationEntity $application
     */
    public function removeApplication(ApplicationEntity $application): void {
        $application->setCart(NULL);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function addInversedApplication(ApplicationEntity $application): void {
        $this->applications->add($application);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function removeInversedApplication(ApplicationEntity $application): void {
        $this->applications->removeElement($application);
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
            $event->removeIversedCart($this);
        }
        $this->event = $event;
        if ($event) {
            $event->addIversedCart($this);
        }
    }

    /**
     * @return EarlyEntity
     */
    public function getEarly(): EarlyEntity {
        return $this->early;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly(?EarlyEntity $early): void {
        $this->early = $early;
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
    protected function setState(int $state): void {
        $this->state = $state;
    }

    /**
     * @return SubstituteEntity
     */
    public function getSubstitute(): ?SubstituteEntity {
        return $this->substitute;
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function setSubstitute(?SubstituteEntity $substitute): void {
        if ($this->substitute) {
            $this->substitute->setInversedCart(NULL);
        }
        $this->substitute = $substitute;
        if ($this->substitute) {
            $this->substitute->setInversedCart($this);
        }
    }

    /**
     * @return ReservationEntity
     */
    public function getReservation(): ?ReservationEntity {
        return $this->reservation;
    }

    /**
     * @param ReservationEntity $reservation
     */
    public function setReservation(?ReservationEntity $reservation): void {
        if ($this->reservation) {
            $this->reservation->setInversedCart(NULL);
        }
        $this->reservation = $reservation;
        if ($this->reservation) {
            $this->reservation->setInversedCart($this);
        }
    }

}