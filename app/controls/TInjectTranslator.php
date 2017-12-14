<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 22:29
 */

namespace App\Controls;


use Kdyby\Translation\ITranslator;
use Kdyby\Translation\Translator;

trait TInjectTranslator {
    /** @var Translator */
    private $translator;

    public function injectTranslator(ITranslator $translator){
        $this->translator = $translator;
    }

    public function getTranslator(): ?Translator{
        return $this->translator;
    }
}