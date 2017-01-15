<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\Email;
use App\Model\Entities\Attributes\Name;
use App\Model\Entities\Attributes\Password;
use App\Model\Entities\Attributes\Username;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdministratorEntity extends BaseEntity {
    use Identifier,Username,Password, Name,Email;

}