<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Exceptions\InvalidStateException;
use Ticketer\Model\Database\Attributes\TAddressAttribute;
use Ticketer\Model\Database\Attributes\TBirthDateAttribute;
use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TGenderAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Jedna konkrétní vydaná přihláška
 * @package App\Model\Entities
 * @ORM\Table(name="application")
 * @ORM\Entity
 */
class ApplicationEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TPersonNameAttribute;
    use TGenderAttribute;
    use TAddressAttribute;
    use TBirthDateAttribute;
    use TCreatedAttribute;

    //TODO make it enum
    public const STATE_RESERVED = 1;
    public const STATE_DELEGATED = 2;
    public const STATE_WAITING = 3;
    public const STATE_OCCUPIED = 4;
    public const STATE_FULFILLED = 5;
    public const STATE_CANCELLED = 6;

    /**
     * @return array<int,string>
     */
    public static function getAllStates(): array
    {
        return [
            self::STATE_RESERVED => "Value.Application.State.Reserved",
            self::STATE_DELEGATED => "Value.Application.State.Delegated",
            self::STATE_WAITING => "Value.Application.State.Waiting",
            self::STATE_OCCUPIED => "Value.Application.State.Occupied",
            self::STATE_FULFILLED => "Value.Application.State.Fulfilled",
            self::STATE_CANCELLED => "Value.Application.State.Cancelled",
        ];
    }

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="application", cascade={"persist","remove"}))
     * @var ArrayCollection<int,ChoiceEntity>
     */
    private $choices;

    /**
     * @ORM\ManyToOne(targetEntity="CartEntity", inversedBy="applications")
     * @var CartEntity|null
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="applications")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationEntity", inversedBy="applications")
     * @var ReservationEntity|null
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

    public function __construct(bool $reserved = false)
    {
        parent::__construct();
        if ($reserved) {
            $this->state = self::STATE_RESERVED;
        }
        $this->choices = new ArrayCollection();
        $this->setCreated();
    }

    /**
     * @return null|string
     */
    public function getFriend(): ?string
    {
        return $this->friend;
    }

    /**
     * @param null|string $friend
     */
    public function setFriend(?string $friend): void
    {
        $this->friend = $friend;
    }

    /**
     * @return null|string
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param null|string $info
     */
    public function setInfo(?string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return InsuranceCompanyEntity|null
     */
    public function getInsuranceCompany(): ?InsuranceCompanyEntity
    {
        return $this->insuranceCompany;
    }

    /**
     * @param InsuranceCompanyEntity $insuranceCompany
     */
    public function setInsuranceCompany(InsuranceCompanyEntity $insuranceCompany): void
    {
        $this->insuranceCompany = $insuranceCompany;
    }

    /**
     * @return ChoiceEntity[]
     */
    public function getChoices(): array
    {
        return $this->choices->toArray();
    }

    /**
     * @return ChoiceEntity[][]
     */
    public function getChoicesByAdditionId(): array
    {
        $result = [];
        foreach ($this->getChoices() as $choice) {
            $option = $choice->getOption();
            if (null === $option) {
                continue;
            }
            $addition = $option->getAddition();
            if (null === $addition) {
                continue;
            }
            $additionId = $addition->getId()->toString();
            if (!isset($result[$additionId])) {
                $result[$additionId] = [];
            }
            $result[$additionId][] = $choice;
        }

        return $result;
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice(ChoiceEntity $choice): void
    {
        $choice->setApplication($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice(ChoiceEntity $choice): void
    {
        $choice->setApplication(null);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice(ChoiceEntity $choice): void
    {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function removeInversedChoice(ChoiceEntity $choice): void
    {
        $this->choices->removeElement($choice);
    }

    /**
     * @return CartEntity
     */
    public function getCart(): ?CartEntity
    {
        return $this->cart;
    }

    /**
     * @param CartEntity|null $cart
     */
    public function setCart(?CartEntity $cart): void
    {
        if (null !== $cart) {
            $cartEvent = $cart->getEvent();
            $event = $this->getEvent();
            if (null !== $cartEvent && null === $event) {
                $this->setEvent($cartEvent);
            } elseif (null !== $event && null === $cartEvent) {
                $cart->setEvent($event);
            }
            $cartEvent = $cart->getEvent();
            $event = $this->getEvent();
            if (
                null === $event
                || null === $cartEvent
                || $event->getId() !== $cartEvent->getId()
            ) {
                throw new InvalidInputException("Error.Application.InvalidInput");
            }
        }
        if (null !== $this->cart) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->cart->removeInversedApplication($this);
        }
        $this->cart = $cart;
        if (null !== $cart) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $cart->addInversedApplication($this);
        }
        $this->updateState();
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return int[]
     */
    public static function getStatesReserved(): array
    {
        return [self::STATE_RESERVED, self::STATE_DELEGATED];
    }

    /**
     * @return int[]
     */
    public static function getStatesOccupied(): array
    {
        return [self::STATE_OCCUPIED, self::STATE_FULFILLED, /*self::STATE_RESERVED, self::STATE_DELEGATED*/];
    }

    /**
     * @return int[]
     */
    public static function getStatesNotIssued(): array
    {
        return [self::STATE_CANCELLED];
    }

    public function cancelApplication(): void
    {
        $this->state = self::STATE_CANCELLED;
    }

    public function updateState(): void
    {
        if (in_array($this->getState(), self::getStatesNotIssued(), true)) {
            return;
        }
        if (in_array($this->getState(), self::getStatesReserved(), true)) {
            if (null !== $this->getReservation()) {
                $this->state = self::STATE_DELEGATED;
            } else {
                $this->state = self::STATE_RESERVED;
            }
        }
        $cart = $this->getCart();
        $event = null !== $cart ? $cart->getEvent() : null;
        if (null !== $cart && null !== $event) {
            $this->state = self::STATE_WAITING;
            $required_states = [];
            $required_additions = [];
            $enough = [];
            foreach ($event->getAdditions() as $addition) {
                $additionId = $addition->getId()->toString();
                $state = $addition->getEnoughForState();
                if (null !== $state) {
                    $enough[$additionId] = $state;
                }
                $minState = $addition->getRequiredForState();
                if (null !== $minState) {
                    for ($state = $minState; $state <= self::STATE_FULFILLED; $state++) {
                        if (!isset($required_states[$state])) {
                            $required_states[$state] = [];
                        }
                        $required_states[$state][] = $addition->getId();
                        if (!isset($required_additions[$additionId])) {
                            $required_additions[$additionId] = [];
                        }
                        $required_additions[$additionId][] = $state;
                    }
                }
            }
            $choices = $this->getChoicesByAdditionId();
            foreach ($event->getAdditions() as $addition) {
                $areChoicesPayed = true;
                $additionId = $addition->getId()->toString();
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
                        $index = array_search($additionId, $required_states[$state], true);
                        unset($required_states[$state][$index]);
                    }
                }
            }
            for ($state = self::STATE_WAITING; $state <= self::STATE_FULFILLED; $state++) {
                if ($this->state > $state) {
                    continue;
                }
                if (!array_key_exists($state, $required_states)) {
                    continue;
                }
                /* TODO check if not needed
                 * if (count($required_states[$state]) > 0) {
                    continue;
                }*/
                $this->state = $state;
            }
        }
        if (null !== $event) {
            $event->updateCapacityFull();
        }
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(?EventEntity $event): void
    {
        if (null !== $this->event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->event->removeInversedApplication($this);
        }
        $this->event = $event;
        if (null !== $event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addInversedApplication($this);
        }
    }

    /**
     * @return EventEntity|NULL
     */
    public function getEvent(): ?EventEntity
    {
        return $this->event;
    }

    /**
     * @return ReservationEntity
     */
    public function getReservation(): ?ReservationEntity
    {
        return $this->reservation;
    }

    /**
     * @param ReservationEntity $reservation
     * @throws InvalidInputException
     * @throws InvalidStateException
     */
    public function setReservation(?ReservationEntity $reservation): void
    {
        if (!in_array($this->getState(), self::getStatesReserved(), true)) {
            throw new InvalidStateException("Error.Reservation.Application.InvalidState");
        }
        if (null !== $reservation) {
            $reservationEvent = $reservation->getEvent();
            $event = $this->getEvent();
            if (null !== $reservationEvent && null === $event) {
                $this->setEvent($reservationEvent);
            } elseif (null !== $event && null === $reservationEvent) {
                $reservation->setEvent($event);
            }
            $reservationEvent = $reservation->getEvent();
            $event = $this->getEvent();
            if (
                null === $event
                || null === $reservationEvent
                || $event->getId() !== $reservationEvent->getId()
            ) {
                throw new InvalidInputException("Error.Application.InvalidInput");
            }
        }
        if (null !== $this->reservation) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->reservation->removeInversedApplication($this);
        }
        $this->reservation = $reservation;
        if (null !== $this->reservation) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->reservation->addInversedApplication($this);
        }
        $this->updateState();
    }
}
