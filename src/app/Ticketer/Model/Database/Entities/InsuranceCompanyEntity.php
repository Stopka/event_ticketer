<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TNameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Pojišťovna
 * @package App\Model\Entities
 * @ORM\Entity
 */
class InsuranceCompanyEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TNameAttribute;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $code;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
