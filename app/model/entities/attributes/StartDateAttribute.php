<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait StartDateAttribute {

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $startDate;

    /**
     * @return \DateTime
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    /**
     * @param $date \DateTime|null
     * @return bool
     */
    public function isStarted(\DateTime $date = null){
        if(!$date){
            $date = new \DateTime();
        }
        return $this->getStartDate()->getTimestamp()<=$date->getTimestamp();
    }

}