<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\EmailAttribute;
use App\Model\Entities\Attributes\IdentifierAttribute;
use App\Model\Entities\Attributes\PasswordAttribute;
use App\Model\Entities\Attributes\PersonNameAttribute;
use App\Model\Entities\Attributes\UsernameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdministratorEntity extends BaseEntity {
    use IdentifierAttribute,UsernameAttribute,PasswordAttribute, PersonNameAttribute,EmailAttribute;

}