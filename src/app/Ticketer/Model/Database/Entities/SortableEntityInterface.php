<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

interface SortableEntityInterface extends EntityInterface
{

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @param int $position
     */
    public function setPosition(int $position): void;
}
