<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait CapacityAttribute {

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $capacity;

    /**
     * @return int
     */
    public function getCapacity() {
        return $this->capacity;
    }

    /**
     * @param int $capacity
     */
    public function setCapacity($capacity) {
        $this->capacity = $capacity;
    }

}