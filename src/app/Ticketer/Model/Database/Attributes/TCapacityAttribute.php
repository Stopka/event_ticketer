<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TCapacityAttribute
{

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer|null
     */
    private $capacity;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $capacityFull = false;

    /**
     * @return int
     */
    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    /**
     * @param int|null $issuedCount
     * @return bool
     */
    public function isCapacityFull(?int $issuedCount = null): bool
    {
        if (null === $issuedCount) {
            return $this->capacityFull;
        }

        return $this->capacityFull || ($this->isCapacitySet() && 0 === $this->getCapacityLeft($issuedCount));
    }

    public function isCapacitySet(): bool
    {
        return null !== $this->getCapacity();
    }

    /**
     * @param int|null $issuedCount
     * @return int|null
     */
    public function getRealCapacityLeft(?int $issuedCount): ?int
    {
        if (null === $issuedCount) {
            $issuedCount = $this->countCapacityUsage();
        }
        if (!$this->isCapacitySet()) {
            return null;
        }

        return (int)$this->getCapacity() - $issuedCount;
    }

    /**
     * @param int|null $issuedCount
     * @return int|null
     */
    public function getCapacityLeft(?int $issuedCount = null): ?int
    {
        if (!$this->isCapacitySet()) {
            return null;
        }
        if ($this->isCapacityFull()) {
            return 0;
        }

        return max((int)$this->getRealCapacityLeft($issuedCount), 0);
    }

    /**
     * @param int|null $capacity
     * @return $this
     */
    public function setCapacity(?int $capacity): self
    {
        $changed = $this->capacity !== $capacity;
        $this->capacity = $capacity;
        if ($changed) {
            $this->updateCapacityFull();
        }

        return $this;
    }

    /**
     * @param bool $capacityFull
     * @return $this
     */
    protected function setCapacityFull(bool $capacityFull = true): self
    {
        $this->capacityFull = $capacityFull;

        return $this;
    }

    abstract public function countCapacityUsage(): int;

    protected function getCapacityUsage(?int $issuedCount): int
    {
        $capacity = $this->getCapacity();
        if (null !== $capacity && $this->isCapacityFull()) {
            return $capacity;
        }

        return min($issuedCount ?? $this->countCapacityUsage(), (int)$this->getCapacity());
    }

    public function updateCapacityFull(): void
    {
        $this->setCapacityFull($this->isCapacitySet() && $this->countCapacityUsage() >= $this->getCapacity());
    }
}
