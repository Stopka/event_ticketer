<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TCapacityAttribute {

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $capacity;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $capacityFull = false;

    /**
     * @return int
     */
    public function getCapacity(): ?int {
        return $this->capacity;
    }

    /**
     * @param $issued_count integer|null
     * @return boolean
     */
    public function isCapacityFull(?int $issued_count = null): bool {
        if ($issued_count === NULL) {
            return $this->capacityFull;
        }
        return $this->capacityFull || ($this->isCapacitySet() && !$this->getCapacityLeft($issued_count));
    }

    public function isCapacitySet(): bool{
        return $this->getCapacity()!==NULL;
    }

    /**
     * @param $issued_count integer
     * @return integer|null
     */
    public function getRealCapacityLeft($issued_count): ?int{
        if(!$this->isCapacitySet()){
            return NULL;
        }
        return $this->getCapacity()-$issued_count;
    }

    /**
     * @param $issued_count integer
     * @return integer|null
     */
    public function getCapacityLeft(int $issued_count): ?int{
        if(!$this->isCapacitySet()){
            return NULL;
        }
        if($this->isCapacityFull()){
            return 0;
        }
        return max($this->getCapacity()-$issued_count,0);
    }

    /**
     * @param int $capacity
     * @return $this
     */
    public function setCapacity(?int $capacity): self {
        $this->capacity = $capacity;
        return $this;
    }

    /**
     * @param bool $capacityFull
     * @return $this
     */
    public function setCapacityFull(bool $capacityFull = true): self {
        $this->capacityFull = $capacityFull;
        return $this;
    }

}