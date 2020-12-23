<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TPositionAttribute
{

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $position = PHP_INT_MAX;

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
