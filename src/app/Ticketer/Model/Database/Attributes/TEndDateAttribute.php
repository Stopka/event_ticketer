<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait TEndDateAttribute
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime|null
     */
    private $endDate;

    /**
     * @return DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     */
    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @param DateTime|null $date
     * @return bool
     */
    public function isEnded(DateTime $date = null)
    {
        if (null === $date) {
            $date = new DateTime();
        }
        $endDate = $this->getEndDate();

        return null !== $endDate && $endDate->getTimestamp() <= $date->getTimestamp();
    }
}
