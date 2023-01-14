<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TPasswordAttribute
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $password;

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hash;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        $valid = password_verify($password, (string)$this->password);
        if ($valid && $this->isPasswordRehashNeeded()) {
            $this->setPassword($password);
        }

        return $valid;
    }

    /**
     * @return bool
     */
    protected function isPasswordRehashNeeded(): bool
    {
        return password_needs_rehash((string)$this->password, PASSWORD_DEFAULT);
    }
}
