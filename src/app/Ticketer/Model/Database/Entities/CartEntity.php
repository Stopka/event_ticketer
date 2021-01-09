<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TNumberAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Ticketer\Model\Database\Attributes\TPhoneAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objednávka, seskupení naráz vydaných přihlášek
 * @package App\Model\Entities
 * @ORM\Table(name="cart")
 * @ORM\Entity
 */
class CartEntity extends BaseEntity implements NumberableInterface
{
    use TIdentifierAttribute;
    use TPersonNameAttribute;
    use TEmailAttribute;
    use TPhoneAttribute;
    use TCreatedAttribute;

    public const STATE_ORDERED = 1;

    /**
     * @ORM\OneToOne(targetEntity="CartNumberEntity", cascade={"persist","remove"})
     * @var CartNumberEntity
     */
    private CartNumberEntity $number;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $state = self::STATE_ORDERED;

    /**
     * @ORM\OneToMany(targetEntity="ApplicationEntity", mappedBy="cart"))
     * @var ArrayCollection<int,ApplicationEntity>
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="carts")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="EarlyEntity", inversedBy="carts")
     * @var EarlyEntity|null
     */
    private $early;

    /**
     * @ORM\OneToOne(targetEntity="SubstituteEntity", inversedBy="cart")
     * @var SubstituteEntity|null
     */
    private $substitute;

    /**
     * CartEntity constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->applications = new ArrayCollection();
        $this->number = new CartNumberEntity();
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
        $application->setCart($this);
    }

    /**
     * @param ApplicationEntity $application
     */
    public function removeApplication(ApplicationEntity $application): void
    {
        $application->setCart(null);
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
     * @internal
     */
    public function removeInversedApplication(ApplicationEntity $application): void
    {
        $this->applications->removeElement($application);
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
            $this->event->removeInversedCart($this);
        }
        $this->event = $event;
        if (null !== $event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addInversedCart($this);
        }
    }

    /**
     * @return EarlyEntity|null
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

    /**
     * @param int $state
     */
    protected function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return SubstituteEntity|null
     */
    public function getSubstitute(): ?SubstituteEntity
    {
        return $this->substitute;
    }

    /**
     * @param SubstituteEntity|null $substitute
     */
    public function setSubstitute(?SubstituteEntity $substitute): void
    {
        if (null !== $this->substitute) {
            $this->substitute->setInversedCart(null);
        }
        $this->substitute = $substitute;
        if (null !== $this->substitute) {
            $this->substitute->setInversedCart($this);
        }
    }

    public function getNumber(): int
    {
        return $this->number->getId();
    }
}
