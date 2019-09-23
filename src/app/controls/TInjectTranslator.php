<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 22:29
 */

namespace App\Controls;


use Kdyby\Translation\ITranslator;

trait TInjectTranslator {
    /** @var ITranslator */
    private $translator;

    public function injectTranslator(ITranslator $translator){
        $this->translator = $translator;
    }

    public function getTranslator(): ?ITranslator{
        return $this->translator;
    }
}