<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ramsey\Uuid\Uuid;

/**
 * Základ všech entit
 * @package App\Model\Entities
 */
abstract class BaseEntity implements IEntity
{
    use TArrayValue;

    /**
     * @ORM\Column(type="integer")
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @var Uuid
     */
    private $uid;

    abstract protected function resetId(): void;

    public function __construct()
    {
        $this->resetId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid->toString();
    }
}
