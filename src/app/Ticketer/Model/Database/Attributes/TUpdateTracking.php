<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

trait TUpdateTracking
{
    abstract protected function onUpdate();
}
