<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait TCreatedAttribute
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime|null
     */
    private $created;

    /**
     * @return DateTime
     */
    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime|NULL $created
     */
    protected function setCreated(?DateTime $created = null): void
    {
        if (null === $created) {
            $created = new DateTime();
        }
        $this->created = $created;
    }

    /**
     *
     */
    protected function resetCreated(): void
    {
        $this->setCreated();
    }
}
