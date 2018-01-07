<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TPositionAttribute {

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $position = PHP_INT_MAX;

    /**
     * @return int
     */
    public function getPosition(): int {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void {
        $this->position = $position;
    }

}