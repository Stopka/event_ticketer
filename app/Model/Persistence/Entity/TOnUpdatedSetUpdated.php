<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.18
 * Time: 10:15
 */

namespace App\Model\Persistence\Entity;


trait TOnUpdatedSetUpdated {

    abstract function setUpdated();

    protected function onUpdatedSetUpdated() {
        $this->setUpdated();
    }
}