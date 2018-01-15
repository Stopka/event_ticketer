<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TCreatedAttribute;
use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Náhradník
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ReservationEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TEmailAttribute, TCreatedAttribute;

    const STATE_WAITING = 0;
    const STATE_ORDERED = 1;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_WAITING;

    /**
     * @ORM\OneToOne(targetEntity="CartEntity", mappedBy="reservation")
     * @var CartEntity
     */
    private $cart;

    /**
     * CartEntity constructor
     */
    public function __construct() {
        $this->setCreated();
    }

    /**
     * @return CartEntity
     */
    public function getCart(): ?CartEntity {
        return $this->cart;
    }

    /**
     * @param CartEntity $cart
     */
    public function setCart(?CartEntity $cart) {
        $cart->setSubstitute($this);
    }

    /**
     * @param CartEntity $cart
     */
    public function setInversedCart(?CartEntity $cart) {
        $this->cart = $cart;
    }

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void {
        $this->state = $state;
    }

}