<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

/**
 * Základ všech entit
 * @package App\Model\Entities
 */
abstract class BaseEntity extends \Kdyby\Doctrine\Entities\BaseEntity {
    use TArrayValue;

    abstract function resetId(): void;

    public function __construct() {
        $this->resetId();
    }
}