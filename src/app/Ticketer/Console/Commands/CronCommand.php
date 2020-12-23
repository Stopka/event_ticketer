<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Ticketer\Model\CronService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends AbstractCommand
{

    /** @var CronService */
    private $cronService;

    public function __construct(CronService $cronService, ?string $name = null)
    {
        parent::__construct($name);
        $this->cronService = $cronService;
    }

    protected function configure(): void
    {
        $this->setName('cron:hourly')
            ->setDescription('Runs cron tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cronService->run();

        return self::SUCCESS;
    }
}
