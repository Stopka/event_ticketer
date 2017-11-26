<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TStartDateAttribute {

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $startDate;

    /**
     * @return \DateTime
     */
    public function getStartDate(): ?\DateTime {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(?\DateTime $startDate) {
        $this->startDate = $startDate;
    }

    /**
     * @param $date \DateTime|null
     * @return bool
     */
    public function isStarted(?\DateTime $date = null): bool{
        if(!$date){
            $date = new \DateTime();
        }
        $startDate = $this->getStartDate();
        return !$startDate||$startDate->getTimestamp()<=$date->getTimestamp();
    }

}