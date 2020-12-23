<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use Nette\Localization\ITranslator;

trait TInjectTranslator
{
    /** @var ITranslator */
    private ITranslator $translator;

    /**
     * @param ITranslator $translator
     * @internal
     */
    protected function injectTranslator(ITranslator $translator): void
    {
        $this->translator = $translator;
    }

    public function getTranslator(): ITranslator
    {
        return $this->translator;
    }
}
