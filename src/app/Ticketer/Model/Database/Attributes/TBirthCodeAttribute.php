<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TBirthCodeAttribute
{

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $birthCode;

    /**
     * @return string|null
     */
    public function getBirthCode()
    {
        return $this->birthCode;
    }

    /**
     * @param string|null $birthCode
     */
    public function setBirthCode($birthCode)
    {
        $this->birthCode = $birthCode;
    }
}
