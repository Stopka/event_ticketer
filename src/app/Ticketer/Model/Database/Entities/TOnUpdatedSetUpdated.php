<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

trait TOnUpdatedSetUpdated
{

    abstract protected function setUpdated();

    protected function onUpdatedSetUpdated()
    {
        $this->setUpdated();
    }
}
