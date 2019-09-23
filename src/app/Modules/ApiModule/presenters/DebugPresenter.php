<?php

namespace App\ApiModule\Presenters;

use App\Model;
use Tracy\Debugger;


/**
 * Base presenter for admin application presenters.
 */
abstract class DebugPresenter extends BasePresenter {
    public function startup() {
        parent::startup();
        if (Debugger::$productionMode) {
            throw new Model\Exception\NotReadyException("Not availible in production");
        }
    }


}