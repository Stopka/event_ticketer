<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Dtos\Uuid;

trait TIdentifierAttribute
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     * @var Uuid
     */
    protected Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::generate();
    }

    public function __clone()
    {
        $this->id = Uuid::generate();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}
