<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticketer\Model\Dtos\Uuid;

class GenerateUidCommand extends AbstractCommand
{
    private const ARG_COUNT = 'count';

    protected static $defaultName = 'debug:generate-uid';

    protected function configure(): void
    {
        $this->setName('debug:generate-uid')
            ->setDescription('Generates unique id string')
            ->addArgument(self::ARG_COUNT, InputArgument::OPTIONAL, 'How many uids should be generated', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $countArgument */
        $countArgument = $input->getArgument(self::ARG_COUNT);
        $count = (int)($countArgument ?? 1);
        for ($i = 0; $i < $count; $i++) {
            $output->writeln(Uuid::generate()->toString());
        }

        return self::SUCCESS;
    }
}
