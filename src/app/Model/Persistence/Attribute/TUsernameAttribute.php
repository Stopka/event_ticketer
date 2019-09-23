<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TUsernameAttribute {

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $username;

    /**
     * @return string
     */
    public function getUsername(): ?string {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username) {
        $this->username = $username;
    }


}