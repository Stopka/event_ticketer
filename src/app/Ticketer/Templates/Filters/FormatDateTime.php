<?php

declare(strict_types=1);

namespace Ticketer\Templates\Filters;

use DateTimeInterface;
use Ticketer\Model\DateFormatter;

class FormatDateTime implements FilterInterface
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

    public function getName(): string
    {
        return 'formatDateTime';
    }
}
