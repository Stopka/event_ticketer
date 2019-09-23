<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPasswordAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use App\Model\Persistence\Attribute\TUsernameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdministratorEntity extends BaseEntity {
    use TIdentifierAttribute, TUsernameAttribute, TPasswordAttribute, TPersonNameAttribute, TEmailAttribute;

}