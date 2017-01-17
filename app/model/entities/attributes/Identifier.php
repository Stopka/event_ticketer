<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:43
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait Identifier {
    use \Kdyby\Doctrine\Entities\Attributes\Identifier;

    protected function resetId(){
        $this->id = null;
    }
}