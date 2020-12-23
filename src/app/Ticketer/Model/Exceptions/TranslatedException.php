<?php

declare(strict_types=1);

namespace Ticketer\Model\Exceptions;

use JsonException;
use Nette\Localization\ITranslator;
use Throwable;

/**
 * Description of RuntimeException
 *
 * @author stopka
 */
class TranslatedException extends Exception
{
    /** @var int|null */
    private $count;

    /** @var array<mixed> */
    private $parameters;

    /** @var string */
    private $bareMessage;

    /**
     * TranslatedException constructor.
     * @param string $message
     * @param int|null $count
     * @param array<mixed> $params
     * @param int $code
     * @param Throwable|null $previous
     * @throws JsonException
     */
    public function __construct(
        string $message = "",
        ?int $count = null,
        array $params = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->bareMessage = $message;
        $this->count = $count;
        $this->parameters = $params;
        parent::__construct($this->buildMessage($message, $count, $params), $code, $previous);
    }

    /**
     * @param string $message
     * @param int|null $count
     * @param array<mixed> $params
     * @return string
     * @throws JsonException
     */
    protected function buildMessage(string $message = "", ?int $count = null, array $params = []): string
    {
        return json_encode(
            [
                'message' => $message,
                'count' => $count,
                'params' => $params,
            ],
            JSON_THROW_ON_ERROR
        );
    }


    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @return array<mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getBareMessage(): string
    {
        return $this->bareMessage;
    }

    /**
     * Vrátí lokalizovanou zprávu o chybě
     * @param ITranslator $translator
     * @return string
     */
    public function getTranslatedMessage(ITranslator $translator)
    {
        $e = $this->getPrevious();
        $message = $this->getBareMessage();
        $count = $this->getCount();
        $parameters = $this->getParameters();
        if (!isset($parameters['reason']) && $e instanceof self) {
            /** @var TranslatedException $e */
            $submessage = $e->getTranslatedMessage($translator);
            $parameters['reason'] = $submessage;
        }

        return $translator->translate($message, $count, $parameters);
    }
}
