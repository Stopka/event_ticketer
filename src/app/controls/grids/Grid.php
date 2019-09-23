<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:37
 */

namespace App\Controls\Grids;


use App\Controls\Grids\Components\Button;
use App\Controls\Grids\Components\Event;
use App\Controls\Grids\Components\Href;
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
        if ($this->dateFormatter && !$dateFormat) {
            $dateColumn->setDateFormat($this->dateFormatter->getDateFormat());
        }
        return $dateColumn;
    }

    public function addColumnTime($name, $label, $dateFormat = NULL) {
        $dateColumn = parent::addColumnDate($name, $label, $dateFormat);
        if ($this->dateFormatter && !$dateFormat) {
            $dateColumn->setDateFormat($this->dateFormatter->getTimeFormat());
        }
        return $dateColumn;
    }

    public function addColumnDateTime($name, $label, $dateFormat = NULL) {
        $dateColumn = parent::addColumnDate($name, $label, $dateFormat);
        if ($this->dateFormatter && !$dateFormat) {
            $dateColumn->setDateFormat($this->dateFormatter->getDateTimeFormat());
        }
        return $dateColumn;
    }

    public function addActionHref($name, $label, $destination = NULL, array $arguments = []) {
        return new Href($this, $name, $label, $destination, $arguments);
    }

    public function addActionEvent($name, $label, $onClick = NULL) {
        return new Event($this, $name, $label, $onClick);
    }

    public function addButton($name, $label = NULL, $destination = NULL, array $arguments = []) {
        return new Button($this, $name, $label, $destination, $arguments);
    }


}