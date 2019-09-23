<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 22:29
 */

namespace App\Controls;


use App\Model\DateFormatter;

trait TInjectDateFormatter {
    /** @var DateFormatter */
    private $dateFormatter;

    public function injectDateFormatter(DateFormatter $dateFormatter) {
        $this->dateFormatter = $dateFormatter;
    }

    public function getDateFormatter(): ?DateFormatter {
        return $this->dateFormatter;
    }
}