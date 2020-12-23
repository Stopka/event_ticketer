<?php

declare(strict_types=1);

namespace Ticketer\Responses\SpreadsheetResponse\Renderers;

use ArrayAccess;
use Ticketer\Responses\SpreadsheetResponse\Column;

trait ColumnTrait
{
    private Column $column;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    /**
     * @return Column
     */
    protected function getColumn(): Column
    {
        return $this->column;
    }

    /**
     * @param ArrayAccess<string,mixed> $data
     * @return mixed
     */
    protected function getColumnData(ArrayAccess $data)
    {
        return $this->getColumn()->getColumnData($data);
    }
}
