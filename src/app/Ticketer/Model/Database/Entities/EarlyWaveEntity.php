<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TNameAttribute;
use Ticketer\Model\Database\Attributes\TStartDateAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Seznam uživatelů s přednostím právem k přihláškám , kteří mají společné datum spuštění
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyWaveEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TStartDateAttribute;
    use TNameAttribute;

    public function __construct()
    {
        parent::__construct();
        $this->earlies = new ArrayCollection();
    }

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="earlyWaves")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="EarlyEntity", mappedBy="earlyWave", cascade={"persist","remove"})
     * @var ArrayCollection<int,EarlyEntity>
     */
    private $earlies;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $inviteSent = false;

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity
    {
        return $this->event;
    }

    /**
     * @param EventEntity|null $event
     */
    public function setEvent(?EventEntity $event): void
    {
        if (null !== $this->event) {
            $this->event->removeInversedEarlyWave($this);
        }
        $this->event = $event;
        if (null !== $event) {
            $event->addInversedEarlyWave($this);
        }
    }

    /**
     * @return EarlyEntity[]
     */
    public function getEarlies(): array
    {
        return $this->earlies->toArray();
    }

    /**
     * @param EarlyEntity $early
     */
    public function addEarly(EarlyEntity $early): void
    {
        $early->setEarlyWave($this);
    }

    /**
     * @param EarlyEntity $early
     */
    public function removeEarly(EarlyEntity $early): void
    {
        $early->setEarlyWave(null);
    }

    /**
     * @param EarlyEntity $early
     * @internal
     */
    public function addInversedEarly(EarlyEntity $early): void
    {
        $this->earlies->add($early);
    }

    /**
     * @param EarlyEntity $early
     * @internal
     */
    public function removeInversedEarly(EarlyEntity $early): void
    {
        $this->earlies->removeElement($early);
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(): bool
    {
        $event = $this->getEvent();
        if (null === $event) {
            return false;
        }

        return $event->isActive() && $this->isStarted();
    }

    /**
     * @return bool
     */
    public function isInviteSent(): bool
    {
        return $this->inviteSent;
    }

    /**
     * @param bool $inviteSent
     */
    public function setInviteSent(bool $inviteSent = true): void
    {
        $this->inviteSent = $inviteSent;
    }
}
