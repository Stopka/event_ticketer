<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Ticketer\Model\Exceptions\InvalidInputException;
use Nette\Mail\Message;
use Nette\SmartObject;

class EmailMessageFactory
{
    use SmartObject;

    /** @var  array<int,string|null> */
    private $from;
    /** @var array<int,string|null> */
    private $replyTo;

    /**
     * EmailMessageFactory constructor.
     * @param string|array<int,string|null> $form
     * @param null|string|array<int,string|null> $replyTo
     */
    public function __construct($form, $replyTo = null)
    {
        $this->setFrom($form);
        $this->setReplyTo($replyTo);
    }

    /**
     * @return array<int,string|null>
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @param array<int,string|null>|string $from
     */
    public function setFrom($from): void
    {
        $this->from = $this->getAddressNamePair($from);
    }

    /**
     * @return array<int,string|null>
     */
    public function getReplyTo(): array
    {
        return $this->replyTo;
    }

    /**
     * @param string|array<int,string|null>|null $replyTo
     */
    public function setReplyTo($replyTo = null): void
    {
        if (null === $replyTo) {
            $this->replyTo = [null, null];

            return;
        }
        $this->replyTo = $this->getAddressNamePair($replyTo);
    }

    /**
     * @return Message
     */
    public function create(): Message
    {
        $message = new Message();
        [$address, $name] = $this->getFrom();
        $message->setFrom((string)$address, $name);
        [$address, $name] = $this->getReplyTo();
        if (null !== $address) {
            $message->addReplyTo($address, $name);
        }

        return $message;
    }

    /**
     * @param string|array<string|null> $param
     * @return array<string|null> address,name
     * @throws InvalidInputException
     */
    protected function getAddressNamePair($param): array
    {
        if (is_string($param)) {
            return [$param, null];
        }
        if (isset($param[0]) && is_string($param[0])) {
            return [
                $param[0],
                isset($param[1]) && is_string($param[1]) ? $param[1] : null,
            ];
        }
        throw new InvalidInputException("Invalid email address input");
    }
}
