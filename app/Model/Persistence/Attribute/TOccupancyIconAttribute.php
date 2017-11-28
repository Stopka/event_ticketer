<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TOccupancyIconAttribute {

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $occupancyIcon;

    /**
     * @return string
     */
    public function getOccupancyIcon(): ?string {
        return $this->occupancyIcon;
    }

    /**
     * @param string $occupancyIcon
     */
    public function setOccupancyIcon(string $occupancyIcon) {
        $this->occupancyIcon = $occupancyIcon;
    }

}