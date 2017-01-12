<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;


trait BirthDate {

    /**
     * @ORM\Column(type="datetime")
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