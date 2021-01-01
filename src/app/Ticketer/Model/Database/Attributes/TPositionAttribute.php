<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Complex\conjugateTest;
use Doctrine\ORM\Mapping as ORM;

trait TPositionAttribute
{
    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $position = 2147483647;

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
