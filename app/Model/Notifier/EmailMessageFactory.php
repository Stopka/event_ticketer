<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;


use App\Model\Exception\InvalidInputException;
use Nette\Mail\Message;
use Nette\Object;

class EmailMessageFactory extends Object {

    /** @var  string[] */
    private $from;
    /** @var string[] */
    private $replyTo;

    /**
     * EmailMessageFactory constructor.
     * @param string|string[] $form
     * @param null|string|string[] $replyTo
     */
    public function __construct($form, $replyTo = NULL) {
        $this->setFrom($form);
        $this->setReplyTo($replyTo);
    }

    /**
     * @return string[]
     */
    public function getFrom(): array {
        return $this->from;
    }

    /**
     * @param string[]|string $from
     */
    public function setFrom($from): void {
        $this->from = $this->getAddressNamePair($from);
    }

    /**
     * @return string[]
     */
    public function getReplyTo(): array {
        return $this->replyTo;
    }

    /**
     * @param string|string[] $replyTo
     */
    public function setReplyTo($replyTo = null): void {
        if (!$replyTo) {
            $this->replyTo = [null, null];
            return;
        }
        $this->replyTo = $this->getAddressNamePair($replyTo);
    }

    /**
     * @return Message
     */
    public function create(): Message {
        $message = new Message();
        list($address, $name) = $this->getFrom();
        $message->setFrom($address, $name);
        list($address, $name) = $this->getReplyTo();
        if ($address) {
            $message->addReplyTo($address, $name);
        }
        return $message;
    }

    /**
     * @param $param string|string[]
     * @return string[] address,name
     * @throws InvalidInputException
     */
    protected function getAddressNamePair($param): array {
        if (is_string($param)) {
            return [$param, null];
        }
        if (is_array($param) && isset($param[0]) && is_string($param[0])) {
            return [
                $param[0],
                isset($param[1]) && is_string($param[1]) ? $param[1] : null
            ];
        }
        throw new InvalidInputException("Invalid email address input");
    }

}