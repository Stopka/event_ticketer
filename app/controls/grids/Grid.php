<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:37
 */

namespace App\Controls\Grids;


use App\Model\DateFormatter;

class Grid extends \Grido\Grid {

    /** @var DateFormatter */
    protected $dateFormatter;

    /**
     * @return DateFormatter
     */
    public function getDateFormatter(): ?DateFormatter {
        return $this->dateFormatter;
    }

    /**
     * @param DateFormatter $dateFormatter
     */
    public function setDateFormatter(?DateFormatter $dateFormatter): void {
        $this->dateFormatter = $dateFormatter;
    }

    public function addColumnDate($name, $label, $dateFormat = NULL) {
        $dateColumn = parent::addColumnDate($name, $label, $dateFormat);
        if($this->dateFormatter && !$dateFormat) {
            $dateColumn->setDateFormat($this->dateFormatter->getDateFormat());
        }
        return $dateColumn;
    }

    public function addColumnTime($name, $label, $dateFormat = NULL) {
        $dateColumn = parent::addColumnDate($name, $label, $dateFormat);
        if($this->dateFormatter && !$dateFormat) {
            $dateColumn->setDateFormat($this->dateFormatter->getTimeFormat());
        }
        return $dateColumn;
    }

    public function addColumnDateTime($name, $label, $dateFormat = NULL) {
        $dateColumn = parent::addColumnDate($name, $label, $dateFormat);
        if($this->dateFormatter && !$dateFormat) {
            $dateColumn->setDateFormat($this->dateFormatter->getDateTimeFormat());
        }
        return $dateColumn;
    }

}