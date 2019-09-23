<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TPersonNameAttribute {

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $lastName;

    /**
     * @return string
     */
    public function getFirstName(): ?string {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(?string $firstName): void {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(?string $lastName): void {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFullName(): string {
        return $this->getFirstName().' '.$this->getLastName();
    }

}