<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TEndDateAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\ORM\Mapping as ORM;

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

    //TODO make it enum
    public const STATE_WAITING = 0;
    public const STATE_ACTIVE = 1;
    public const STATE_ORDERED = 2;
    public const STATE_OVERDUE = 4;

    /**
     * CartEntity constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCreated();
    }

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $state = self::STATE_WAITING;

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
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    public function isOrdered(): bool
    {
        return self::STATE_ORDERED === $this->getState();
    }

    /**
     * @return int[]
     */
    public static function getActivableStates(): array
    {
        return [
            self::STATE_WAITING,
            self::STATE_OVERDUE,
        ];
    }

    public function activate(?\DateInterval $interval = null): void
    {
        if (!in_array($this->getState(), self::getActivableStates(), true)) {
            return;
        }
        $this->setState(self::STATE_ACTIVE);
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
        return self::STATE_ACTIVE === $this->getState() && !$this->isEnded();
    }

    /**
     * @param int $state
     */
    protected function setState(int $state): void
    {
        $this->state = $state;
    }

    public function updateState(): void
    {
        if (self::STATE_WAITING === $this->getState()) {
            return;
        }
        if (null !== $this->getCart()) {
            $this->setState(self::STATE_ORDERED);

            return;
        }
        if ($this->isEnded()) {
            $this->setState(self::STATE_OVERDUE);

            return;
        }
        $this->setState(self::STATE_ACTIVE);
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
