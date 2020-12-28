<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Database\Enums\GenderEnum;

trait TGenderAttribute
{

    /**
     * @ORM\Column(type="gender_enum", nullable=true)
     * @var GenderEnum|null
     */
    private ?GenderEnum $gender;

    /**
     * @return GenderEnum|null
     */
    public function getGender(): ?GenderEnum
    {
        return $this->gender;
    }

    /**
     * @param GenderEnum|null $gender
     */
    public function setGender(?GenderEnum $gender): void
    {
        $this->gender = $gender;
    }
}
