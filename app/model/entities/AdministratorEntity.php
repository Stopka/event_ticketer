<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdministratorEntity extends BaseEntity {

    /**
     * Username
     * @var string
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * Heslo
     * @var string
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * Email
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;
}