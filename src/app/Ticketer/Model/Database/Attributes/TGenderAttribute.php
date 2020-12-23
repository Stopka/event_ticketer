<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TGenderAttribute
{

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    private ?int $gender;

    /**
     * @return GenderEnum|null
     */
    public function getGender(): ?GenderEnum
    {
        return null !== $this->gender ? new GenderEnum($this->gender) : null;
    }

    /**
     * @param GenderEnum|null $gender
     */
    public function setGender(?GenderEnum $gender): void
    {
        $this->gender = null !== $gender ? $gender->getValue() : null;
    }
}
