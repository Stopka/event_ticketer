<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use Nette\Localization\ITranslator;

trait TFlashTranslatedMessage
{
    /**
     * @param string $message
     * @param string $type
     * @return mixed
     */
    abstract protected function flashMessage(string $message, string $type = 'info');

    /**
     * @return ITranslator
     */
    abstract protected function getTranslator(): ITranslator;

    /**
     * @param string $message
     * @param FlashMessageTypeEnum|null $type
     * @param array<mixed> $args
     */
    public function flashTranslatedMessage(
        string $message,
        ?FlashMessageTypeEnum $type = null,
        ...$args
    ): void {
        if (null === $type) {
            $type = FlashMessageTypeEnum::INFO();
        }
        $translator = $this->getTranslator();
        $message = $translator->translate($message, ...$args);
        $this->flashMessage($message, $type->getValue());
    }
}
