<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.18
 * Time: 11:51
 */

namespace App\Console;


use App\Model\Persistence\Attribute\TIdentifierAttribute;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateUidCommand extends Command {
    const ARG_COUNT = 'count';

    protected function configure() {
        $this->setName('debug:generate-uid')
            ->setDescription('Generates unique id string')
            ->addArgument(self::ARG_COUNT, InputArgument::OPTIONAL, 'How many uids should be generated', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $count = $input->getArgument(self::ARG_COUNT);
        for ($i = 0; $i < $count; $i++) {
            $output->writeln(TIdentifierAttribute::generateUid());
        }
    }


}