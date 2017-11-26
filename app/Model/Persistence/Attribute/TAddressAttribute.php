<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TAddressAttribute {

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $address;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $zip;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $country;

    /**
     * @return string
     */
    public function getAddress():string {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(?string $address):self {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): ?string {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(?string $city): self {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getZip(): ?string {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip(?string $zip): self {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(?string $country): self {
        $this->country = $country;
        return $this;
    }
}