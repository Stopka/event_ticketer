<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Nette\Application\UI\InvalidLinkException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Notifiers\EarlyWaveInviteNotifier;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEarlyInvitesCommand extends AbstractCommand
{

    private const ARG_WAVE_ID = 'waveId';

    /** @var EarlyWaveInviteNotifier */
    private $earlyWaveInviteNotifier;

    protected static $defaultName = 'debug:sendEarlyInvites';

    public function __construct(EarlyWaveInviteNotifier $cartCreatedNotifier, ?string $name = null)
    {
        parent::__construct($name);
        $this->earlyWaveInviteNotifier = $cartCreatedNotifier;
    }


    protected function configure(): void
    {
        $this->setName('debug:sendEarlyInvites')
            ->setDescription('Sends emails to earlies')
            ->addArgument(self::ARG_WAVE_ID, InputArgument::REQUIRED, 'Which wave should be sent');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidLinkException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $waveIdArgument */
        $waveIdArgument = $input->getArgument(self::ARG_WAVE_ID);
        $uuid = Uuid::fromString($waveIdArgument);
        $this->earlyWaveInviteNotifier->sendDebugEarlyWaveInvites($uuid);

        return self::SUCCESS;
    }
}
