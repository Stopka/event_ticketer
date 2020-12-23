<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Nette\SmartObject;

/**
 * Class CronService
 * @package App\Model
 */
class CronService
{
    use SmartObject;

    /** @var array<callable> */
    public $onCronRun = [];

    public function run(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onCronRun();
    }
}
