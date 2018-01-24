<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.18
 * Time: 11:51
 */

namespace App\Console;


use App\Model\Notifier\EarlyWaveInviteNotifier;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEarlyInvitesCommand extends Command {

    const ARG_WAVE_ID = 'waveId';

    /** @var EarlyWaveInviteNotifier */
    private $earlyWaveInviteNotifier;

    public function __construct(EarlyWaveInviteNotifier $earlyWaveInviteNotifier, ?string $name = null) {
        parent::__construct($name);
        $this->earlyWaveInviteNotifier = $earlyWaveInviteNotifier;
    }


    protected function configure() {
        $this->setName('debug:sendEarlyInvites')
            ->setDescription('Sends emails to earlies')
            ->addArgument(self::ARG_WAVE_ID, InputArgument::OPTIONAL, 'Which wave should be sent', 1);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $waveId = $input->getArgument(self::ARG_WAVE_ID);
        $this->earlyWaveInviteNotifier->sendDebugEarlyWaveInvites($waveId);
    }


}