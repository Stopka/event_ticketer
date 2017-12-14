<?php

namespace App\Model\Exception;

use Kdyby\Translation\ITranslator;
use Throwable;

/**
 * Description of RuntimeException
 *
 * @author stopka
 */
class Exception extends \RuntimeException {
    /** @var int|null */
    private $count;

    /** @var array */
    private $parameters;

    /** @var string */
    private $bareMessage;

    public function __construct(string $message = "", ?int $count ,  array $params = [], int $code = 0, Throwable $previous = null) {
        $this->bareMessage = $message;
        $this->count = $count;
        $this->parameters = $params;
        parent::__construct($this->buildMessage($message, $count, $params), $code, $previous);
    }

    protected function buildMessage(string $message ="", ?int $count = null, array $params = []) :string {
        return json_encode([
            'message'=>$message,
            'count'=>$count,
            'params'=>$params
        ]);
    }


    /**
     * @return int|null
     */
    public function getCount(): ?int {
        return $this->count;
    }

    /**
     * @return array
     */
    public function getParameters(): array {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getBareMessage(): string {
        return $this->bareMessage;
    }

    /**
     * Vrátí lokalizovanou zprávu o chybě
     * @param ITranslator $translator
     * @return \string
     */
    public function getTranslatedMessage(ITranslator $translator) {
        $e = $this->getPrevious();
        $message = $this->getBareMessage();
        $count = $this->getCount();
        $parameters = $this->getParameters();
        if (!isset($parameters['reason']) && is_subclass_of($e, Exception::class)) {
            /** @var Exception $e */
            $submessage = $e->getTranslatedMessage($translator);
            $parameters['reason'] = $submessage;
        }
        return $translator->translate($message, $count, $parameters, 'Error');
    }
}


