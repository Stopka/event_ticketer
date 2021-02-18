<?php

declare(strict_types=1);

namespace Ticketer\Templates\Filters;

use DateTimeInterface;
use Ticketer\Model\DateFormatter;

class FormatDateTime
{
    private DateFormatter $dateFormatter;

    public function __construct(DateFormatter $dateFormatter)
    {
        $this->dateFormatter = $dateFormatter;
    }

    public function __invoke(DateTimeInterface $dateTime): string
    {
        return $this->dateFormatter->getDateTimeString($dateTime);
    }
}
