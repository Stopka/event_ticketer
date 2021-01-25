<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Ticketer\Model\Database\Handlers\ResolveApplicationStatesHandler;
use Ticketer\Model\Dtos\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveApplicationStatesCommand extends AbstractCommand
{

    private const ARG_EVENT_ID = 'eventId';

    private ResolveApplicationStatesHandler $resolveApplicationStatesHandler;

    protected static $defaultName = 'debug:resolveApplicationStates';

    public function __construct(ResolveApplicationStatesHandler $resolveApplicationStatesHandler, ?string $name = null)
    {
        parent::__construct($name);
        $this->resolveApplicationStatesHandler = $resolveApplicationStatesHandler;
    }


    protected function configure(): void
    {
        $this->setName('debug:resolveApplicationStates')
            ->setDescription('Updates states of all applications in event')
            ->addArgument(self::ARG_EVENT_ID, InputArgument::REQUIRED, 'Which event applications should be resolved');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $eventIdArgument */
        $eventIdArgument = $input->getArgument(self::ARG_EVENT_ID);
        $uuid = Uuid::fromString($eventIdArgument);
        $this->resolveApplicationStatesHandler->resolveApplicationStates($uuid);

        return self::SUCCESS;
    }
}
