<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Ticketer\Model\Cron\CronService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends AbstractCommand
{

    private CronService $cronService;

    protected static $defaultName = 'cron:hourly';

    public function __construct(CronService $cronService)
    {
        parent::__construct();
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
