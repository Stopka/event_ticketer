<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TEndDateAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Database\Enums\SubstituteStateEnum;

/**
 * Náhradník
 * @package App\Model\Entities
 * @ORM\Entity
 */
class SubstituteEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TPersonNameAttribute;
    use TEmailAttribute;
    use TEndDateAttribute;
    use TCreatedAttribute;

    /**
     * @ORM\Column(type="substitute_state_enum")
     * @var SubstituteStateEnum
     */
    private SubstituteStateEnum $state;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="substitutes")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="EarlyEntity")
     * @var EarlyEntity|null
     */
    private $early;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $count;

    /**
     * @ORM\OneToOne(targetEntity="CartEntity", mappedBy="substitute")
     * @var CartEntity|null
     */
    private $cart;

    /**
     * CartEntity constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCreated();
        $this->state = SubstituteStateEnum::WAITING();
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
            $this->event->removeInversedSubstitute($this);
        }
        $this->event = $event;
        if (null !== $event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addIversedSubstitute($this);
        }
    }

    /**
     * @return EarlyEntity
     */
    public function getEarly(): ?EarlyEntity
    {
        return $this->early;
    }

    /**
     * @param EarlyEntity|null $early
     */
    public function setEarly(?EarlyEntity $early): void
    {
        $this->early = $early;
    }

    /**
     * @return SubstituteStateEnum
     */
    public function getState(): SubstituteStateEnum
    {
        return $this->state;
    }

    public function isOrdered(): bool
    {
        return $this->getState()->equals(SubstituteStateEnum::ORDERED());
    }

    public function activate(?\DateInterval $interval = null): void
    {
        if (!$this->getState()->isActivable()) {
            return;
        }
        $this->setState(SubstituteStateEnum::ACTIVE());
        if (null !== $interval) {
            $date = new \DateTime();
            $date = $date->add($interval);
        } else {
            $date = null;
        }
        $this->setEndDate($date);
        $this->updateState();
    }

    public function isActive(): bool
    {
        return $this->getState()->equals(SubstituteStateEnum::ACTIVE())
            && !$this->isEnded();
    }

    /**
     * @param SubstituteStateEnum $state
     */
    protected function setState(SubstituteStateEnum $state): void
    {
        $this->state = $state;
    }

    public function updateState(): void
    {
        if ($this->getState()->equals(SubstituteStateEnum::WAITING())) {
            return;
        }
        if (null !== $this->getCart()) {
            $this->setState(SubstituteStateEnum::ORDERED());

            return;
        }
        if ($this->isEnded()) {
            $this->setState(SubstituteStateEnum::OVERDUE());

            return;
        }
        $this->setState(SubstituteStateEnum::ACTIVE());
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
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
        if (null === $cart) {
            return;
        }
        $cart->setSubstitute($this);
    }

    /**
     * @param CartEntity|null $cart
     */
    public function setInversedCart(?CartEntity $cart): void
    {
        $this->cart = $cart;
        $this->updateState();
    }
}
