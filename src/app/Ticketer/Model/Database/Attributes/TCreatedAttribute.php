<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait TCreatedAttribute
{

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $created = null;

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @param DateTimeImmutable|NULL $created
     */
    protected function setCreated(?DateTimeImmutable $created = null): void
    {
        if (null === $created) {
            $created = new DateTimeImmutable();
        }
        $this->created = $created;
    }

    /**
     *
     */
    protected function resetCreated(): void
    {
        $this->setCreated();
    }
}
