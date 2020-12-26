<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Uživatel s přednostním právem přihlášky
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyEntity extends BaseEntity
{
    use TPersonNameAttribute;
    use TEmailAttribute;


    /**
     * @ORM\ManyToOne(targetEntity="EarlyWaveEntity", inversedBy="earlies")
     * @var EarlyWaveEntity|null
     */
    private $earlyWave;

    /**
     * @ORM\OneToMany(targetEntity="CartEntity", mappedBy="early")
     * @var ArrayCollection<int,CartEntity>
     */
    private $carts;

    public function __construct()
    {
        parent::__construct();
        $this->carts = new ArrayCollection();
    }

    /**
     * @return EarlyWaveEntity
     */
    public function getEarlyWave(): ?EarlyWaveEntity
    {
        return $this->earlyWave;
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function setEarlyWave(?EarlyWaveEntity $earlyWave): void
    {
        if (null !== $this->earlyWave) {
            $this->earlyWave->removeInversedEarly($this);
        }
        $this->earlyWave = $earlyWave;
        if (null !== $earlyWave) {
            $earlyWave->addInversedEarly($this);
        }
    }

    /**
     * @return CartEntity[]
     */
    public function getCarts(): array
    {
        return $this->carts->toArray();
    }

    /**
     * @param CartEntity $cart
     */
    public function addCart(CartEntity $cart): void
    {
        $cart->setEarly($this);
    }

    /**
     * @param CartEntity $cart
     * @internal
     */
    public function addIversedCart(CartEntity $cart): void
    {
        $this->carts->add($cart);
    }

    /**
     * @param CartEntity $cart
     */
    public function removeCart(CartEntity $cart): void
    {
        $cart->setEarly(null);
    }

    /**
     * @param CartEntity $cart
     * @internal
     */
    public function removeIversedCart(CartEntity $cart): void
    {
        $this->carts->removeElement($cart);
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(): bool
    {
        $wave = $this->getEarlyWave();
        if (null === $wave) {
            return false;
        }

        return $wave->isReadyToRegister();
    }
}
