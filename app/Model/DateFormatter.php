<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 10.12.17
 * Time: 16:09
 */

namespace App\Model;


use Nette\SmartObject;

class DateFormatter {
    use SmartObject;

    /**
     * DateFormatter constructor.
     * @param string $dateFormat
     * @param string $timeFormat
     * @param string $dateTimeFormat
     */
    public function __construct(string $dateFormat, string $timeFormat, string $dateTimeFormat) {
        $this->setDateFormat($dateFormat);
        $this->setTimeFormat($timeFormat);
        $this->setDateTimeFormat($dateTimeFormat);
    }

    /** @var string */
    private $dateFormat;

    /** @var string */
    private $timeFormat;

    /** @var string */
    private $dateTimeFormat;

    /**
     * @return string
     */
    public function getDateFormat(): string {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat(string $dateFormat): void {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @return string
     */
    public function getTimeFormat(): string {
        return $this->timeFormat;
    }

    /**
     * @param string $timeFormat
     */
    public function setTimeFormat(string $timeFormat): void {
        $this->timeFormat = $timeFormat;
    }

    /**
     * @return string
     */
    public function getDateTimeFormat(): string {
        return $this->dateTimeFormat;
    }

    /**
     * @param string $dateTimeFormat
     */
    public function setDateTimeFormat(string $dateTimeFormat): void {
        $replace = [
            '%date%' => $this->getDateFormat(),
            '%time%' => $this->getTimeFormat()
        ];
        $dateTimeFormat = str_replace(array_keys($replace),array_values($replace),$dateTimeFormat);
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * @param \DateTime|null $dateTime
     * @return string
     */
    public function getDateString(?\DateTime $dateTime): string {
        if(!$dateTime){
            return "";
        }
        return $dateTime->format($this->getDateFormat());
    }

    /**
     * @param \DateTime|null $dateTime
     * @return string
     */
    public function getTimeString(?\DateTime $dateTime): string {
        if(!$dateTime){
            return "";
        }
        return $dateTime->format($this->getTimeFormat());
    }

    /**
     * @param \DateTime|null $dateTime
     * @return string
     */
    public function getDateTimeString(?\DateTime $dateTime): string {
        if(!$dateTime){
            return "";
        }
        return $dateTime->format($this->getDateTimeFormat());
    }
}