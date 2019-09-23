<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TGenderAttribute {

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    private $gender;

    /**
     * @return int
     */
    public function getGender(): ?int {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender(?int $gender): void {
        $this->gender = $gender;
    }

}