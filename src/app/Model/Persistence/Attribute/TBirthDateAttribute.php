<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TBirthDateAttribute {

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime
     */
    private $birthDate;

    /**
     * @return \DateTime
     */
    public function getBirthDate(): ?\DateTime {
        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     */
    public function setBirthDate(?\DateTime $birthDate): void {
        $this->birthDate = $birthDate;
    }

}