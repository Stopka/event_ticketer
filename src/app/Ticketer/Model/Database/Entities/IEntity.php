<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Přídavek k přihlášce (Přihláška, Faktura, Doprava, Tričko...)
 * @package App\Model\Entities
 * @ORM\Entity
 */
interface IEntity
{
    /** @return int|null */
    public function getId();

    public function getUid(): string;
}
