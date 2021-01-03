<?php

declare(strict_types=1);

namespace Ticketer\Controls\Grids\Columns;

use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Filter\FilterSelect;

trait OveridedColumnTrait
{
    /**
     * @param mixed[] $replacements
     * @return static
     */
    public function setReplacement(array $replacements): Column
    {
        $translator = $this->grid->getTranslator();

        return parent::setReplacement(
            array_map(
                static fn(string $item): string => $translator->translate($item),
                $replacements
            )
        );
    }

    /**
     * @param mixed[] $options
     * @param string|null $column
     * @return FilterSelect
     */
    public function setFilterSelect(array $options, ?string $column = null): FilterSelect
    {
        return parent::setFilterSelect($options, $column)
            ->setTranslateOptions(true);
    }
}
