<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;

class HomepagePresenter extends BasePresenter
{
    /**
     * @throws AbortException
     */
    public function renderDefault(): void
    {
        $this->redirect('Event:default');
    }
}
