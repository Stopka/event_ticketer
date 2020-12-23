<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait TStartDateAttribute
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime|null
     */
    private $startDate;

    /**
     * @return DateTime
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     */
    public function setStartDate(?DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @param DateTime|null $date
     * @return bool
     */
    public function isStarted(?DateTime $date = null): bool
    {
        if (null === $date) {
            $date = new DateTime();
        }
        $startDate = $this->getStartDate();

        return null === $startDate || $startDate->getTimestamp() <= $date->getTimestamp();
    }
}
