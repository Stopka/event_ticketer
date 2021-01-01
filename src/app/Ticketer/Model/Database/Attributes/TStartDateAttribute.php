<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait TStartDateAttribute
{

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    private $startDate;

    /**
     * @return DateTimeImmutable
     */
    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeImmutable|null $startDate
     */
    public function setStartDate(?DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @param DateTimeImmutable|null $date
     * @return bool
     */
    public function isStarted(?DateTimeImmutable $date = null): bool
    {
        if (null === $date) {
            $date = new DateTimeImmutable();
        }
        $startDate = $this->getStartDate();

        return null === $startDate || $startDate->getTimestamp() <= $date->getTimestamp();
    }
}
