<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TUpdatedAttribute
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updated;

    /**
     * @return \DateTime
     */
    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime|NULL $updated
     */
    protected function setUpdated(?\DateTime $updated = null): void
    {
        if (!$updated) {
            $updated = new \DateTime();
        }
        $this->updated = $updated;
    }

    /**
     *
     */
    protected function resetUpdated(): void
    {
        $this->updated = null;
    }
}
