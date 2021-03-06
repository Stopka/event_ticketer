<?php

declare(strict_types=1);

namespace Ticketer\Model;

use DateTime;
use DateTimeInterface;
use Nette\SmartObject;

class DateFormatter
{
    use SmartObject;

    /**
     * DateFormatter constructor.
     * @param string $dateFormat
     * @param string $timeFormat
     * @param string $dateTimeFormat
     */
    public function __construct(string $dateFormat, string $timeFormat, string $dateTimeFormat)
    {
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
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     */
    public function setDateFormat(string $dateFormat): void
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @return string
     */
    public function getTimeFormat(): string
    {
        return $this->timeFormat;
    }

    /**
     * @param string $timeFormat
     */
    public function setTimeFormat(string $timeFormat): void
    {
        $this->timeFormat = $timeFormat;
    }

    /**
     * @return string
     */
    public function getDateTimeFormat(): string
    {
        return $this->dateTimeFormat;
    }

    /**
     * @param string $dateTimeFormat
     */
    public function setDateTimeFormat(string $dateTimeFormat): void
    {
        $replace = [
            '%date%' => $this->getDateFormat(),
            '%time%' => $this->getTimeFormat(),
        ];
        $dateTimeFormat = str_replace(array_keys($replace), array_values($replace), $dateTimeFormat);
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * @param DateTimeInterface|null $dateTime
     * @return string
     */
    public function getDateString(?DateTimeInterface $dateTime): string
    {
        if (null === $dateTime) {
            return "";
        }

        return $dateTime->format($this->getDateFormat());
    }

    /**
     * @param DateTimeInterface|null $dateTime
     * @return string
     */
    public function getTimeString(?DateTimeInterface $dateTime): string
    {
        if (null === $dateTime) {
            return "";
        }

        return $dateTime->format($this->getTimeFormat());
    }

    /**
     * @param DateTimeInterface|null $dateTime
     * @return string
     */
    public function getDateTimeString(?DateTimeInterface $dateTime): string
    {
        if (null === $dateTime) {
            return "";
        }

        return $dateTime->format($this->getDateTimeFormat());
    }
}
