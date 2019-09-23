<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TPhoneAttribute {

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $phone;

    /**
     * @return string
     */
    public function getPhone(): ?string {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(?string $phone) {
        $this->phone = $phone;
    }

}