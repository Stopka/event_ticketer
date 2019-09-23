<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 17.12.17
 * Time: 11:32
 */

namespace App\Controls;


use Kdyby\Translation\ITranslator;

trait TFlashTranslatedMessage{

    abstract function flashMessage($message,$type = 'info');

    abstract function getTranslator(): ?ITranslator;

    /**
     * @param string $message
     * @param string $type
     * @param int|null $count
     * @param array $args
     * @param null|string $domain
     * @param null|string $locale
     */
    public function flashTranslatedMessage(string $message,string $type = 'info', ?int $count = null, array $args = [], ?string  $domain = null, ?string $locale = null){
        $translator = $this->getTranslator();
        if($translator){
            $message = $translator->translate($message,$count,$args,$domain,$locale);
        }
        $this->flashMessage($message, $type);
    }
}