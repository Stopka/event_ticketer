<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.18
 * Time: 10:15
 */

namespace App\Model\Persistence\Entity;


use Nette\Utils\Strings;

trait TOnUpdated {
    protected function onUpdated() {
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (Strings::startsWith('onUpdated', $method) && $method !== 'onUpdated') {
                call_user_func([$this, $method]);
            }
        }
    }
}