<?php

declare(strict_types=1);

namespace Ticketer\Responses\SpreadsheetResponse\Renderers;

use ArrayAccess;

/**
 * Class TextRenderer
 * @package Ticketer\Responses\SpreadsheetResponse\Renderers
 */
class TextRenderer
{
    use ColumnTrait;

    /**
     * @param ArrayAccess<string,mixed> $data
     * @return string
     */
    public function __invoke(ArrayAccess $data): string
    {
        $col = $this->getColumnData($data);

        return (string)$col;
    }
}
