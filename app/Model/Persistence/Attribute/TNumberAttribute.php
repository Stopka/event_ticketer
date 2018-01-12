<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TNumberAttribute {

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $number;

    /**
     * @return int
     */
    public function getNumber(): int {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void {
        $this->number = $number;
    }
}