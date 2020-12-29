<?php

declare(strict_types=1);

namespace Ticketer\Controls;

use Nette\Application\UI\Control as NetteControl;
use Ticketer\Presenters\BasePresenter;

abstract class Control extends NetteControl
{
    use TFlashTranslatedMessage;
    use TInjectTranslator;

    public function __construct(ControlDependencies $controlDependencies)
    {
        $this->injectTranslator($controlDependencies->getTranslator());
    }

    /**
     * @return BasePresenter
     */
    public function getPresenter(): BasePresenter
    {
        /** @var BasePresenter $presenter */
        $presenter = parent::getPresenter();

        return $presenter;
    }
}
