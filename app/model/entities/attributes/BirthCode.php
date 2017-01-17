<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait BirthCode {

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $birthCode;

    /**
     * @return string
     */
    public function getBirthCode() {
        return $this->birthCode;
    }

    /**
     * @param string $birthCode
     */
    public function setBirthCode($birthCode) {
        $this->birthCode = $birthCode;
    }

}