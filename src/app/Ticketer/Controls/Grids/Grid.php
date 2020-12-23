<?php

declare(strict_types=1);

namespace Ticketer\Controls\Grids;

use Ticketer\Model\DateFormatter;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\DataGrid;

class Grid extends DataGrid
{
    /** @var DateFormatter|null */
    protected ?DateFormatter $dateFormatter;

    /**
     * @return DateFormatter|null
     */
    public function getDateFormatter(): ?DateFormatter
    {
        return $this->dateFormatter;
    }

    /**
     * @param DateFormatter|null $dateFormatter
     */
    public function setDateFormatter(?DateFormatter $dateFormatter): void
    {
        $this->dateFormatter = $dateFormatter;
    }

    /**
     * @param string $key
     * @param string $name
     * @param string|null $column
     * @return ColumnDateTime
     */
    public function addColumnDate(
        string $key,
        string $name,
        ?string $column = null
    ): ColumnDateTime {
        $dateColumn = parent::addColumnDateTime($key, $name, $column);
        if (null !== $this->dateFormatter) {
            $dateColumn->setFormat($this->dateFormatter->getDateFormat());
        }

        return $dateColumn;
    }

    /**
     * @param string $key
     * @param string $name
     * @param string|null $column
     * @return ColumnDateTime
     */
    public function addColumnTime(
        string $key,
        string $name,
        ?string $column = null
    ): ColumnDateTime {
        $dateColumn = parent::addColumnDateTime($key, $name, $column);
        if (null !== $this->dateFormatter) {
            $dateColumn->setFormat($this->dateFormatter->getTimeFormat());
        }

        return $dateColumn;
    }

    /**
     * @param string $key
     * @param string $name
     * @param string|null $column
     * @return ColumnDateTime
     */
    public function addColumnDateTime(
        string $key,
        string $name,
        ?string $column = null
    ): ColumnDateTime {
        $dateColumn = parent::addColumnDateTime($key, $name, $column);
        if (null !== $this->dateFormatter) {
            $dateColumn->setFormat($this->dateFormatter->getDateTimeFormat());
        }

        return $dateColumn;
    }
}
