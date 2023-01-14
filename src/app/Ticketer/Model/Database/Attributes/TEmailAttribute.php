<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TEmailAttribute
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $email;

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }
}
