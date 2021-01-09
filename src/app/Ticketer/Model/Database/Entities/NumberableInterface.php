<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

interface NumberableInterface
{
    public function getNumber(): int;
}
