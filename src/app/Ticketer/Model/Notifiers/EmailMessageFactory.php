<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Nette\Mail\Message;

class EmailMessageFactory
{
    private string $from;

    private ?string $fromName;

    private ?string $replyTo;

    private ?string $replyToName;

    /**
     * EmailMessageFactory constructor.
     * @param string $form
     * @param string|null $fromName
     * @param string|null $replyTo
     * @param string|null $replyToName
     */
    public function __construct(
        string $form,
        ?string $fromName = null,
        ?string $replyTo = null,
        ?string $replyToName = null
    ) {
        $this->setFrom($form, $fromName);
        $this->setReplyTo($replyTo, $replyToName);
    }

    /**
     * @param string $address
     * @param string|null $name
     */
    public function setFrom(string $address, ?string $name = null): void
    {
        $this->from = $address;
        $this->fromName = $name;
    }

    /**
     * @param string|null $address
     * @param string|null $name
     */
    public function setReplyTo(?string $address = null, ?string $name = null): void
    {
        $this->replyTo = $address;
        $this->replyToName = $name;
    }

    /**
     * @return Message
     */
    public function create(): Message
    {
        $message = new Message();
        $message->setFrom($this->from, $this->fromName);
        if (null !== $this->replyTo) {
            $message->addReplyTo($this->replyTo, $this->replyToName);
        }

        return $message;
    }
}
