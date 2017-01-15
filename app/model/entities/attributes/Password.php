<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait Password {

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $password;

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param $password
     * @return bool
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    /**
     * @return bool
     */
    public function isPasswordRehashNeeded() {
        return password_needs_rehash($this->password, PASSWORD_DEFAULT);
    }


}