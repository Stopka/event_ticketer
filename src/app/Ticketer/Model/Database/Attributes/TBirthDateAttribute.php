<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait TBirthDateAttribute
{

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var DateTime|null
     */
    private $birthDate;

    /**
     * @return DateTime
     */
    public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param DateTime|null $birthDate
     */
    public function setBirthDate(?DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }
}
