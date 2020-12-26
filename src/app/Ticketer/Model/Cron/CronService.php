<?php

declare(strict_types=1);

namespace Ticketer\Model\Cron;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class CronService
 * @package App\Model
 */
class CronService
{

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }


    public function run(): void
    {
        $this->eventDispatcher->dispatch(new HourCronEvent());
    }
}
