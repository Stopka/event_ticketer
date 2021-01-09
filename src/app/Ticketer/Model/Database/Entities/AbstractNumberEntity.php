<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractNumberEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int|null
     */
    private ?int $id = null;

    public function getId(): int
    {
        return (int)$this->id;
    }
}
