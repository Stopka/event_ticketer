<?php

declare(strict_types=1);

namespace Ticketer\Controls\Grids;

use Ticketer\Controls\Grids\Columns\NumberColumn;
use Ticketer\Controls\Grids\Columns\TextColumn;
use Ticketer\Model\DateFormatter;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

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

    /**
     * @param string $key
     * @param string $name
     * @param string|null $column
     * @return TextColumn
     * @throws DataGridException
     */
    public function addColumnText(
        string $key,
        string $name,
        ?string $column = null
    ): ColumnText {
        $column = $column ?? $key;

        $columnText = new TextColumn($this, $key, $column, $name);
        $this->addColumn($key, $columnText);

        return $columnText;
    }

    /**
     * @param string $key
     * @param string $name
     * @param string|null $column
     * @return NumberColumn
     * @throws DataGridException
     */
    public function addColumnNumber(
        string $key,
        string $name,
        ?string $column = null
    ): ColumnNumber {
        $column = $column ?? $key;

        $columnNumber = new NumberColumn($this, $key, $column, $name);
        $this->addColumn($key, $columnNumber);

        return $columnNumber;
    }
}
