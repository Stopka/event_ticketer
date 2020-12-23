<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TOccupancyIconAttribute
{

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $occupancyIcon;

    /**
     * @return string
     */
    public function getOccupancyIcon(): ?string
    {
        return $this->occupancyIcon;
    }

    /**
     * @param string $occupancyIcon
     */
    public function setOccupancyIcon(?string $occupancyIcon): void
    {
        $this->occupancyIcon = $occupancyIcon;
    }
}
