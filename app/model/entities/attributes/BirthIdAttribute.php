<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

trait BirthIdAttribute {
    use BirthCodeAttribute, BirthDateAttribute, GenderAttribute;

    /**
     * @return string
     */
    public function getBirthIdDate() {
        $date = $this->getBirthDate();
        if(!$date||$this->getGender()===NULL){
            return NULL;
        }
        $year = $date->format('y');
        $month = $date->format('m');
        if($this->getGender()===1){
            $month+=50;
            $month = Strings::padLeft($month,2,'0');
        }
        $day = $date->format('d');
        return $year.$month.$day;
    }

    /**
     * @return string
     */
    public function getBirthId() {
        if(!$this->getBirthIdDate()||$this->getBirthCode()===NULL){
            return null;
        }
        return $this->getBirthIdDate().'/'.$this->getBirthCode();
    }
}