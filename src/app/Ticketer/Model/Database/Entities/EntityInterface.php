<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Dtos\Uuid;

/**
 * @package App\Model\Entities
 * @ORM\Entity
 */
interface EntityInterface
{
    public function getId(): Uuid;
}
