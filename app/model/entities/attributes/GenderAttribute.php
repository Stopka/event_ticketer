<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait GenderAttribute {

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    private $gender;

    /**
     * @return int
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }

}