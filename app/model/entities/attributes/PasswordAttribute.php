<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait PasswordAttribute {

    /**
     * @ORM\Column(type="string", nullable=true)
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
        $valid = password_verify($password, $this->password);
        if($valid && $this->isPasswordRehashNeeded()){
            $this->setPassword($password);
        }
        return $valid;
    }

    /**
     * @return bool
     */
    protected function isPasswordRehashNeeded() {
        return password_needs_rehash($this->password, PASSWORD_DEFAULT);
    }


}