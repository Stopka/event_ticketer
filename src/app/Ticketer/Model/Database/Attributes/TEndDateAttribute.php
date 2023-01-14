<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait TEndDateAttribute
{
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    private $endDate;

    /**
     * @return DateTimeImmutable|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeImmutable|null $endDate
     */
    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @param DateTimeImmutable|null $date
     * @return bool
     */
    public function isEnded(DateTimeImmutable $date = null)
    {
        if (null === $date) {
            $date = new DateTimeImmutable();
        }
        $endDate = $this->getEndDate();

        return null !== $endDate && $endDate->getTimestamp() <= $date->getTimestamp();
    }
}
