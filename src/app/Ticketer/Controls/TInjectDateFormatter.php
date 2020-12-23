<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use Ticketer\Model\DateFormatter;

trait TInjectDateFormatter
{
    /** @var DateFormatter */
    private $dateFormatter;

    public function injectDateFormatter(DateFormatter $dateFormatter): void
    {
        $this->dateFormatter = $dateFormatter;
    }

    public function getDateFormatter(): ?DateFormatter
    {
        return $this->dateFormatter;
    }
}
