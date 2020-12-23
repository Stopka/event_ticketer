<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Nette\Utils\Strings;

trait TOnUpdated
{
    protected function onUpdated()
    {
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (Strings::startsWith('onUpdated', $method) && 'onUpdated' !== $method) {
                call_user_func([$this, $method]);
            }
        }
    }
}
