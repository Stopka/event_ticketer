<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Uživatel s přednostním právem přihlášky
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TEmailAttribute;

    public function __construct() {
        parent::__construct();
        $this->carts = new ArrayCollection();
    }


    /**
     * @ORM\ManyToOne(targetEntity="EarlyWaveEntity", inversedBy="earlies")
     * @var EarlyWaveEntity
     */
    private $earlyWave;

    /**
     * @ORM\OneToMany(targetEntity="CartEntity", mappedBy="early")
     * @var CartEntity[]|ArrayCollection
     */
    private $carts;

    /**
     * @return EarlyWaveEntity
     */
    public function getEarlyWave(): ?EarlyWaveEntity {
        return $this->earlyWave;
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function setEarlyWave(?EarlyWaveEntity $earlyWave): void {
        if($this->earlyWave){
            $this->earlyWave->removeInversedEarly($this);
        }
        $this->earlyWave = $earlyWave;
        if($earlyWave) {
            $earlyWave->addInversedEarly($this);
        }
    }

    /**
     * @return CartEntity[]
     */
    public function getCarts(): array {
        return $this->carts->toArray();
    }

    /**
     * @param CartEntity $cart
     */
    public function addCart(CartEntity $cart): void {
        $cart->setEarly($this);
    }

    /**
     * @param CartEntity $cart
     * @internal
     */
    public function addIversedCart(CartEntity $cart): void {
        $this->carts->add($cart);
    }

    /**
     * @param CartEntity $cart
     */
    public function removeCart(CartEntity $cart): void {
        $cart->setEarly(NULL);
    }

    /**
     * @param CartEntity $cart
     * @internal
     */
    public function removeIversedCart(CartEntity $cart): void {
        $this->carts->removeElement($cart);
    }

    public function __clone() {
        $this->resetId();
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(): bool {
        $wave = $this->getEarlyWave();
        if(!$wave){
            return false;
        }
        return $wave->isReadyToRegister();
    }

}