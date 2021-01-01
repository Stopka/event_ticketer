<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AdditionVisibilityEntity extends BaseEntity
{
    use TArrayValue;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private bool $reservation = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private bool $registration = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private bool $customer = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private bool $preview = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private bool $export = false;

    /**
     * @return bool
     */
    public function isReservation(): bool
    {
        return $this->reservation;
    }

    /**
     * @param bool $reservation
     */
    public function setReservation(bool $reservation): void
    {
        $this->reservation = $reservation;
    }

    /**
     * @return bool
     */
    public function isRegistration(): bool
    {
        return $this->registration;
    }

    /**
     * @param bool $registration
     */
    public function setRegistration(bool $registration): void
    {
        $this->registration = $registration;
    }

    /**
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->customer;
    }

    /**
     * @param bool $customer
     */
    public function setCustomer(bool $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->preview;
    }

    /**
     * @param bool $preview
     */
    public function setPreview(bool $preview): void
    {
        $this->preview = $preview;
    }

    /**
     * @return bool
     */
    public function isExport(): bool
    {
        return $this->export;
    }

    /**
     * @param bool $export
     */
    public function setExport(bool $export): void
    {
        $this->export = $export;
    }

    /**
     * @param callable(self $visibility):bool $criteria
     * @return bool
     */
    public function matches(callable $criteria): bool
    {
        return call_user_func($criteria, $this);
    }
}
