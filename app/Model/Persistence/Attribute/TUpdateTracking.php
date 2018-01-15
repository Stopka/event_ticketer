<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.18
 * Time: 10:24
 */

namespace App\Model\Persistence\Entity;


trait TUpdateTracking {
    abstract protected function onUpdate();
}