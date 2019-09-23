<?php

namespace App\Controls\Menus;

use Kdyby\Translation\ITranslator;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.18
 * Time: 10:55
 */
class Menu extends \Stopka\NetteMenuControl\Menu {
    public function __construct(?ITranslator $translator, string $title, $link, array $linkArgs = []) {
        parent::__construct($translator, $title, $link, $linkArgs);
    }

    /**
     * @param \Nette\Localization\ITranslator|null $translator
     * @return $this
     */
    public function setTranslator(?\Nette\Localization\ITranslator $translator) {
        parent::setTranslator($translator);
        return $this;
    }


}