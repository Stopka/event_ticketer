<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TAddressAttribute
{
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $address;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $zip;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $country;

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @param string|null $zip
     */
    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
}
