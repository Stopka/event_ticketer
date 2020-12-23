<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateUidCommand extends AbstractCommand
{
    private const ARG_COUNT = 'count';

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
        $count = (int)$countArgument;
        for ($i = 0; $i < $count; $i++) {
            $output->writeln(TIdentifierAttribute::generateUid());
        }

        return self::SUCCESS;
    }
}
