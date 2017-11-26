<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;


use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Object;
use Tracy\Debugger;

class EmailService extends Object {

    /** @var  EmailMessageFactory */
    private $emailMessageFactory;

    /** @var  LinkGenerator */
    private $linkGenerator;

    /** @var  IMailer */
    private $mailer;

    /** @var  callable[] */
    public $onEmailSent = Array();

    /**
     * EmailService constructor.
     * @param EmailMessageFactory $emailMessageFactory
     * @param LinkGenerator $linkGenerator
     * @param IMailer $mailer
     */
    public function __construct(EmailMessageFactory $emailMessageFactory, LinkGenerator $linkGenerator, IMailer $mailer) {
        $this->emailMessageFactory = $emailMessageFactory;
        $this->linkGenerator = $linkGenerator;
        $this->mailer = $mailer;
    }

    /**
     * @return Message
     */
    public function createMessage(): Message {
        return $this->emailMessageFactory->create();
    }

    /**
     * @param $dest
     * @param array $params
     * @return string
     * @throws InvalidLinkException
     */
    public function generateLink($dest, $params = []) {
        return $this->linkGenerator->link($dest, $params);
    }

    /**
     * @param Message $message
     * @throws SendException
     */
    public function sendMessage(Message $message): void {
        Debugger::barDump($message);
        $this->mailer->send($message);
        $this->onEmailSent();
    }

}