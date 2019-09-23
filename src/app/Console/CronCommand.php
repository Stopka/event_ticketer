<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.18
 * Time: 11:51
 */

namespace App\Console;


use App\Model\CronService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command {

    /** @var CronService */
    private $cronService;

    public function __construct(CronService $cronService, ?string $name = null) {
        parent::__construct($name);
        $this->cronService = $cronService;
    }

    protected function configure() {
        $this->setName('cron:hourly')
            ->setDescription('Runs cron tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->cronService->run();
    }


}