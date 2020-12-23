<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TIdentifierAttribute
{
    /**
     * @ORM\Column(type="string",unique=true)
     * @var string
     */
    private $uid;


    /**
     * Resets id to null
     */
    protected function resetId(): void
    {
        $this->uid = self::generateUid();
    }

    /**
     * Return uuid without dashes
     * @return null|string
     */
    public function getIdAlphaNumeric(): ?string
    {
        $id = $this->getId();
        if (null === $id) {
            return null;
        }
        /** @var string $result */
        $result = str_replace("-", "", (string)$this->getId());

        return $result;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    public static function generateUid(): string
    {
        $random = (string)random_int(0, 1000000000);
        $hash = hash('sha256', $random);
        $string = substr($hash, 0, 16);
        $prefix = $string . '.';

        return uniqid($prefix, true);
    }
}
