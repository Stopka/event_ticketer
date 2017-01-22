<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait BirthDateAttribute {

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime
     */
    private $birthDate;

    /**
     * @return \DateTime
     */
    public function getBirthDate() {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     */
    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;
    }

}