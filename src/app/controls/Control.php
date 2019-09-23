<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 20:36
 */

namespace App\Controls;


use App\Presenters\BasePresenter;

abstract class Control extends \Nette\Application\UI\Control implements IFlashMessage {
    use TFlashTranslatedMessage,TInjectTranslator;

    public function __construct(ControlDependencies $controlDependencies) {
        parent::__construct();
        $this->injectTranslator($controlDependencies->getTranslator());
    }

    public function getPresenter($throw = true): BasePresenter {
        /** @var BasePresenter $presenter */
        $presenter = parent::getPresenter($throw);
        return $presenter;
    }


}