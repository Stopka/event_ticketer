<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait EndDateAttribute {

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $endDate;

    /**
     * @return \DateTime
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    /**
     * @param $date \DateTime|null
     * @return bool
     */
    public function isEnded(\DateTime $date = null){
        if(!$date){
            $date = new \DateTime();
        }
        $endDate = $this->getEndDate();
        return $endDate&&$endDate->getTimestamp()<=$date->getTimestamp();
    }

}