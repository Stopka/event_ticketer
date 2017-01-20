<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * Administrátor systému
 * @package App\Model\Entities
 */
class BaseEntity extends \Kdyby\Doctrine\Entities\BaseEntity {

    /**
     * @param array $values
     */
    public function setByValueArray($values) {
        foreach ($values as $name => $value) {
            $setterName = 'set'.Strings::capitalize($name);
            if (method_exists($this, $setterName)){
                call_user_func([$this,$setterName],$value);
            }
        }
    }
}