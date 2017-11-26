<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:43
 */

namespace App\Model\Persistence\Attribute;

trait TIdentifierAttribute {
    use \Kdyby\Doctrine\Entities\Attributes\UniversallyUniqueIdentifier;

    protected function resetId(){
        $this->id = null;
    }
}