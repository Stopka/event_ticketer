<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Exception\InvalidInputException;
use App\Model\Exception\InvalidStateException;
use App\Model\Persistence\Attribute\TAddressAttribute;
use App\Model\Persistence\Attribute\TBirthDateAttribute;
use App\Model\Persistence\Attribute\TCreatedAttribute;
use App\Model\Persistence\Attribute\TGenderAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Jedna konkrétní vydaná přihláška
 * @package App\Model\Entities
 * @ORM\Table(name="application")
 * @ORM\Entity
 */
class ApplicationEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TGenderAttribute, TAddressAttribute, TBirthDateAttribute, TCreatedAttribute;

    const STATE_RESERVED = 1;
    const STATE_DELEGATED = 2;
    const STATE_WAITING = 3;
    const STATE_OCCUPIED = 4;
    const STATE_FULFILLED = 5;
    const STATE_CANCELLED = 6;

    public static function getAllStates(): array {
        return [
            self::STATE_RESERVED => "Value.Application.State.Reserved",
            self::STATE_DELEGATED => "Value.Application.State.Delegated",
            self::STATE_WAITING => "Value.Application.State.Waiting",
            self::STATE_OCCUPIED => "Value.Application.State.Occupied",
            self::STATE_FULFILLED => "Value.Application.State.Fulfilled",
            self::STATE_CANCELLED => "Value.Application.State.Cancelled"
        ];
    }

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="application", cascade={"persist","remove"}))
     * @var ChoiceEntity[]
     */
    private $choices;

    /**
     * @ORM\ManyToOne(targetEntity="CartEntity", inversedBy="applications")
     * @var CartEntity
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="applications")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationEntity", inversedBy="applications")
     * @var ReservationEntity
     */
    private $reservation;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_WAITING;

    /**
     * @ORM\ManyToOne(targetEntity="InsuranceCompanyEntity")
     * @var InsuranceCompanyEntity
     */
    private $insuranceCompany;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $friend;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $info;

    public function __construct($reserved = false) {
        parent::__construct();
        if ($reserved) {
            $this->state = self::STATE_RESERVED;
        }
        $this->choices = new ArrayCollection();
        $this->setCreated();
    }

    protected function getLastNumberSearchCriteria(): array {
        return [
            'event.id' => $this->getEvent()->getId()
        ];
    }

    /**
     * @return null|string
     */
    public function getFriend(): ?string {
        return $this->friend;
    }

    /**
     * @param null|string $friend
     */
    public function setFriend(?string $friend): void {
        $this->friend = $friend;
    }

    /**
     * @return null|string
     */
    public function getInfo(): ?string {
        return $this->info;
    }

    /**
     * @param null|string $info
     */
    public function setInfo(?string $info): void {
        $this->info = $info;
    }

    /**
     * @return InsuranceCompanyEntity
     */
    public function getInsuranceCompany(): ?InsuranceCompanyEntity {
        return $this->insuranceCompany;
    }

    /**
     * @param InsuranceCompanyEntity $insuranceCompany
     */
    public function setInsuranceCompany(InsuranceCompanyEntity $insuranceCompany): void {
        $this->insuranceCompany = $insuranceCompany;
    }

    /**
     * @return ChoiceEntity[]
     */
    public function getChoices(): array {
        return $this->choices->toArray();
    }

    /**
     * @return ChoiceEntity[][]
     */
    public function getChoicesByAdditionId(): array {
        $result = [];
        foreach ($this->getChoices() as $choice) {
            $addition = $choice->getOption()->getAddition();
            if (!isset($result[$addition->getId()])) {
                $result[$addition->getId()] = [];
            }
            $result[$addition->getId()][] = $choice;
        }
        return $result;
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice(ChoiceEntity $choice): void {
        $choice->setApplication($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice(ChoiceEntity $choice): void {
        $choice->setApplication(NULL);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice(ChoiceEntity $choice): void {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function removeInversedChoice(ChoiceEntity $choice): void {
        $this->choices->removeElement($choice);
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
    public function setCart(CartEntity $cart) {
        if ($event = $cart->getEvent() && !$this->getEvent()) {
            $this->setEvent($cart->getEvent());
        } else if ($this->getEvent() && !$cart->getEvent()) {
            $cart->setEvent($this->getEvent());
        }
        if ($this->getEvent()->getId() !== $cart->getEvent()->getId()) {
            throw new InvalidInputException("Error.Application.InvalidInput");
        }
        if ($this->cart) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->cart->removeInversedApplication($this);
        }
        $this->cart = $cart;
        if ($cart) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $cart->addInversedApplication($this);
        }
        $this->updateState();
    }

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    public static function getStatesReserved(): array {
        return [self::STATE_RESERVED, self::STATE_DELEGATED];
    }

    public static function getStatesOccupied(): array {
        return [self::STATE_OCCUPIED, self::STATE_FULFILLED, /*self::STATE_RESERVED, self::STATE_DELEGATED*/];
    }

    public static function getStatesNotIssued(): array {
        return [self::STATE_CANCELLED];
    }

    public function cancelApplication(): void {
        $this->state = self::STATE_CANCELLED;
    }

    public function updateState(): void {
        if (in_array($this->getState(), self::getStatesNotIssued())) {
            return;
        }
        if (in_array($this->getState(), self::getStatesReserved())) {
            if ($this->getReservation()) {
                $this->state = self::STATE_DELEGATED;
            } else {
                $this->state = self::STATE_RESERVED;
            }
        }
        if (!$this->getCart()) {
            return;
        }
        $this->state = self::STATE_WAITING;
        $event = $this->getCart()->getEvent();
        $required_states = [];
        $required_additions = [];
        $enough = [];
        foreach ($event->getAdditions() as $addition) {
            if ($state = $addition->getEnoughForState()) {
                $enough[$addition->getId()] = $state;
            }
            if ($min_state = $addition->getRequiredForState()) {
                for ($state = $min_state; $state <= self::STATE_FULFILLED; $state++) {
                    if (!isset($required_states[$state])) {
                        $required_states[$state] = [];
                    }
                    array_push($required_states[$state], $addition->getId());
                    if (!isset($required_additions[$addition->getId()])) {
                        $required_additions[$addition->getId()] = [];
                    }
                    array_push($required_additions[$addition->getId()], $state);
                }
            }
        }
        $choices = $this->getChoicesByAdditionId();
        foreach ($event->getAdditions() as $addition) {
            $areChoicesPayed = true;
            $additionId = $addition->getId();
            if (isset($choices[$additionId])) {
                foreach ($choices[$additionId] as $choice) {
                    if (!$choice->isPayed()) {
                        $areChoicesPayed = false;
                        break;
                    }
                }
            }
            if (isset($enough[$additionId]) && $areChoicesPayed && $enough[$additionId] > $this->state) {
                $this->state = $enough[$additionId];
            }
            if (isset($required_additions[$additionId]) && $areChoicesPayed) {
                foreach ($required_additions[$additionId] as $state) {
                    $index = array_search($additionId, $required_states[$state]);
                    unset($required_states[$state][$index]);
                }
            }
        }
        for ($state = self::STATE_WAITING; $state <= self::STATE_FULFILLED; $state++) {
            if ($this->state > $state) {
                continue;
            }
            if (!isset($required_states[$state])) {
                continue;
            }
            if (count($required_states[$state]) > 0) {
                continue;
            }
            $this->state = $state;
        }
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(?EventEntity $event): void {
        if ($this->event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->removeInversedApplication($this);
        }
        $this->event = $event;
        if ($event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addInversedApplication($this);
        }
    }

    /**
     * @return EventEntity|NULL
     */
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @return ReservationEntity
     */
    public function getReservation(): ?ReservationEntity {
        return $this->reservation;
    }

    /**
     * @param ReservationEntity $reservation
     * @throws InvalidInputException
     * @throws InvalidStateException
     */
    public function setReservation(?ReservationEntity $reservation): void {
        if (!in_array($this->getState(), self::getStatesReserved())) {
            throw new InvalidStateException("Error.Reservation.Application.InvalidState");
        }
        if ($event = $reservation->getEvent() && !$this->getEvent()) {
            $this->setEvent($reservation->getEvent());
        } else if ($this->getEvent() && !$reservation->getEvent()) {
            $reservation->setEvent($this->getEvent());
        }
        if ($this->getEvent()->getId() !== $reservation->getEvent()->getId()) {
            throw new InvalidInputException("Error.Application.InvalidInput");
        }
        if ($this->reservation) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->reservation->removeInversedApplication($this);
        }
        $this->reservation = $reservation;
        if ($this->reservation) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->reservation->addInversedApplication($this);
        }
        $this->updateState();
    }
}