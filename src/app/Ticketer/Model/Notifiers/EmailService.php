<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\SmartObject;
use Tracy\Debugger;

class EmailService
{
    use SmartObject;

    /** @var  EmailMessageFactory */
    private $emailMessageFactory;

    /** @var  LinkGenerator */
    private $linkGenerator;

    /** @var  Mailer */
    private $mailer;

    /** @var  callable[] */
    public $onEmailSent = [];

    /**
     * EmailService constructor.
     * @param EmailMessageFactory $emailMessageFactory
     * @param LinkGenerator $linkGenerator
     * @param Mailer $mailer
     */
    public function __construct(EmailMessageFactory $emailMessageFactory, LinkGenerator $linkGenerator, Mailer $mailer)
    {
        $this->emailMessageFactory = $emailMessageFactory;
        $this->linkGenerator = $linkGenerator;
        $this->mailer = $mailer;
    }

    /**
     * @return Message
     */
    public function createMessage(): Message
    {
        return $this->emailMessageFactory->create();
    }

    /**
     * @param string $dest
     * @param array<mixed> $params
     * @return string
     * @throws InvalidLinkException
     */
    public function generateLink(string $dest, $params = []): string
    {
        return $this->linkGenerator->link($dest, $params);
    }

    /**
     * @param Message $message
     * @throws SendException
     */
    public function sendMessage(Message $message): void
    {
        Debugger::barDump($message);
        $this->mailer->send($message);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onEmailSent($message);
    }
}
