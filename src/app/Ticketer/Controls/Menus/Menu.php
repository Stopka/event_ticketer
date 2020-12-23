<?php

declare(strict_types=1);

namespace Ticketer\Controls\Menus;

use Nette\Localization\ITranslator;
use Stopka\NetteMenuControl\ISubmenuFactory;
use Stopka\NetteMenuControl\Menu as StopkaMenu;

class Menu extends StopkaMenu
{
    /**
     * Menu constructor.
     * @param ISubmenuFactory $submenufactory
     * @param ITranslator|null $translator
     * @param string $title
     * @param string|callable|null $link
     * @param array<mixed> $linkArgs
     */
    public function __construct(
        ISubmenuFactory $submenufactory,
        ?ITranslator $translator,
        string $title,
        $link,
        array $linkArgs = []
    ) {
        parent::__construct($submenufactory, $translator, $title, $link, $linkArgs);
    }

    /**
     * @param ITranslator|null $translator
     * @return $this
     */
    public function setTranslator(?ITranslator $translator): self
    {
        parent::setTranslator($translator);

        return $this;
    }
}
