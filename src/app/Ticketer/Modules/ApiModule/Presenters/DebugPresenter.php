<?php

declare(strict_types=1);

namespace Ticketer\Modules\ApiModule\Presenters;

use Ticketer\Model\Exceptions\NotReadyException;
use Tracy\Debugger;

/**
 * Base presenter for admin application presenters.
 */
abstract class DebugPresenter extends BasePresenter
{
    public function startup(): void
    {
        parent::startup();
        if (Debugger::$productionMode) {
            throw new NotReadyException('Not availible in production');
        }
    }
}
